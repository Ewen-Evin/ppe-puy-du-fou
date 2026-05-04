package com.example.puy_du_fou;

import android.Manifest;
import android.annotation.SuppressLint;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.view.Gravity;
import android.view.View;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.HorizontalScrollView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.NavHelper;
import com.example.puy_du_fou.util.Session;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.floatingactionbutton.FloatingActionButton;

import org.json.JSONArray;
import org.json.JSONObject;
import org.osmdroid.config.Configuration;
import org.osmdroid.tileprovider.tilesource.TileSourceFactory;
import org.osmdroid.util.GeoPoint;
import org.osmdroid.views.MapView;
import org.osmdroid.views.overlay.Marker;
import org.osmdroid.views.overlay.Polyline;
import org.osmdroid.views.overlay.mylocation.GpsMyLocationProvider;
import org.osmdroid.views.overlay.mylocation.MyLocationNewOverlay;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Set;

public class CarteActivity extends AppCompatActivity
        implements SensorEventListener, LocationListener {

    private static final int    REQ_LOCATION     = 1001;
    private static final double AUTO_ADVANCE_M   = 40.0; // avance auto quand à <40 m

    // ─── Modèles ─────────────────────────────────────────────────────────────

    static class EtapeGps {
        int    ordre;
        String libelle;
        String lieuNom;
        double lat, lng;
        String heureDebut;
        String heureFin;
        String heureArrivee;
    }

    static class LieuPoint {
        int    id;
        String nom;
        String typeLieu;
        double lat, lng;

        String typeLabel() {
            switch (typeLieu) {
                case "spectacle":   return "Spectacle";
                case "restaurant":  return "Restaurant";
                case "hotel":       return "Hôtel";
                case "accueil":     return "Accueil";
                case "allee":       return "Allée";
                default:            return "Zone";
            }
        }

        String typeColor() {
            switch (typeLieu) {
                case "spectacle":   return "#C62828"; // rouge
                case "restaurant":  return "#E65100"; // orange
                case "hotel":       return "#1565C0"; // bleu
                case "accueil":     return "#2E7D32"; // vert
                case "allee":       return "#4527A0"; // violet
                default:            return "#37474F"; // gris foncé
            }
        }
    }

    // ─── Vues ────────────────────────────────────────────────────────────────

    private MapView              mapView;
    private MyLocationNewOverlay locationOverlay;
    private ProgressBar          progress;
    private TextView             txtEmpty, txtNextName, txtDistance,
                                 txtWalkTime, txtWalkDist, txtShowTime,
                                 txtStep, txtEta, txtArrow;
    private FloatingActionButton btnCenter;

    // ─── Données ─────────────────────────────────────────────────────────────

    private final List<EtapeGps>  etapes        = new ArrayList<>();
    private final List<LieuPoint> lieux         = new ArrayList<>();
    private double                vitesseMarche = 4.0;
    private int                   idVisite      = 0;
    private int                   currentStep   = 0; // étape en cours de navigation

    // Mode navigation ponctuel (browsing → "Y aller")
    private LieuPoint browseTarget = null;

    // ─── Overlays dynamiques ─────────────────────────────────────────────────

    private Polyline currentLegPolyline = null; // trait user → prochain spectacle

    // ─── Popup info lieu ─────────────────────────────────────────────────────
    private LinearLayout lieuInfoCard = null;

    // ─── Barre filtre / légende ───────────────────────────────────────────────
    private HorizontalScrollView filterBar = null;
    private final Set<String>           activeFilters = new HashSet<>(
            Arrays.asList("spectacle","restaurant","hotel","accueil","allee","zone"));
    private final Map<String, Button>   filterChips   = new HashMap<>();

    // ─── Capteurs ────────────────────────────────────────────────────────────

    private SensorManager  sensorManager;
    private Sensor         rotationSensor;
    private float          azimuthSmoothed = 0f;
    private boolean        compassActive   = false;
    private LocationManager locationManager;

    // ─── Lifecycle ───────────────────────────────────────────────────────────

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Configuration.getInstance().load(this, getSharedPreferences("osmdroid", MODE_PRIVATE));
        Configuration.getInstance().setUserAgentValue(getPackageName());
        setContentView(R.layout.activity_carte);

        mapView     = findViewById(R.id.map);
        progress    = findViewById(R.id.progress);
        txtEmpty    = findViewById(R.id.txtEmpty);
        txtNextName = findViewById(R.id.txtNextName);
        txtDistance = findViewById(R.id.txtDistance);
        txtWalkTime = findViewById(R.id.txtWalkTime);
        txtWalkDist = findViewById(R.id.txtWalkDist);
        txtShowTime = findViewById(R.id.txtShowTime);
        txtStep     = findViewById(R.id.txtStep);
        txtEta      = findViewById(R.id.txtEta);
        txtArrow    = findViewById(R.id.txtArrow);
        btnCenter   = findViewById(R.id.btnCenter);

        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        tb.setNavigationOnClickListener(v -> cancelNavigation());

        NavHelper.setup(this, (BottomNavigationView) findViewById(R.id.bottomNav), R.id.nav_carte);

        idVisite       = getIntent().getIntExtra("id_visite", 0);
        // Destination directe depuis SpectacleDetailActivity
        double destLat = getIntent().getDoubleExtra("dest_lat", 0);
        double destLng = getIntent().getDoubleExtra("dest_lng", 0);
        String destNom = getIntent().getStringExtra("dest_nom");
        String destType = getIntent().getStringExtra("dest_type");
        if (destLat != 0 && destLng != 0) {
            browseTarget          = new LieuPoint();
            browseTarget.lat      = destLat;
            browseTarget.lng      = destLng;
            browseTarget.nom      = destNom != null ? destNom : "Destination";
            browseTarget.typeLieu = destType != null ? destType : "spectacle";
        }
        sensorManager  = (SensorManager) getSystemService(SENSOR_SERVICE);
        rotationSensor = sensorManager.getDefaultSensor(Sensor.TYPE_ROTATION_VECTOR);
        locationManager = (LocationManager) getSystemService(LOCATION_SERVICE);

        setupMap();
        buildLieuInfoCard();
        buildFilterBar();

        // Visibilité initiale des cartes de nav
        if (idVisite > 0 || browseTarget != null) {
            // Navigation active (visite ou destination directe)
            if (filterBar != null) filterBar.setVisibility(View.GONE);
        } else {
            // Mode exploration
            findViewById(R.id.navCardTop).setVisibility(View.GONE);
            findViewById(R.id.navCardBottom).setVisibility(View.GONE);
        }

        if (hasLocationPermission()) {
            enableLocationOverlay();
            startLocationUpdates();
            if (idVisite > 0) {
                loadCarteData();
            } else if (browseTarget != null) {
                // Destination directe : centrer sur la dest + activer nav
                GeoPoint dest = new GeoPoint(browseTarget.lat, browseTarget.lng);
                mapView.getController().animateTo(dest);
                mapView.getController().setZoom(17.0);
                GeoPoint pos = getUserPosition();
                updateBrowseLeg(pos);
                refreshBrowseNavCard(pos);
            } else {
                loadLieux();
            }
        } else {
            ActivityCompat.requestPermissions(this,
                    new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, REQ_LOCATION);
        }

        btnCenter.setOnClickListener(v -> centerOnUser());
    }

    @Override
    protected void onResume() {
        super.onResume();
        mapView.onResume();
        NavHelper.syncSelection(this, R.id.nav_carte);
        if (compassActive && rotationSensor != null) {
            sensorManager.registerListener(this, rotationSensor, SensorManager.SENSOR_DELAY_GAME);
        }
        if (hasLocationPermission()) startLocationUpdates();
    }

    @Override
    protected void onPause() {
        super.onPause();
        mapView.onPause();
        sensorManager.unregisterListener(this);
        locationManager.removeUpdates(this);
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        int newId = intent.getIntExtra("id_visite", 0);
        if (newId > 0 && newId != idVisite) {
            idVisite    = newId;
            currentStep = 0;
            clearMap();
            findViewById(R.id.navCardTop).setVisibility(View.VISIBLE);
            findViewById(R.id.navCardBottom).setVisibility(View.VISIBLE);
            if (filterBar != null) filterBar.setVisibility(View.GONE);
            loadCarteData();
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    private boolean hasLocationPermission() {
        return ContextCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED;
    }

    @Override
    public void onRequestPermissionsResult(int req, @NonNull String[] perms, @NonNull int[] results) {
        super.onRequestPermissionsResult(req, perms, results);
        if (req == REQ_LOCATION && results.length > 0
                && results[0] == PackageManager.PERMISSION_GRANTED) {
            enableLocationOverlay();
            startLocationUpdates();
        } else {
            Toast.makeText(this, "GPS refusé – position désactivée", Toast.LENGTH_LONG).show();
        }
        if (idVisite > 0) loadCarteData();
        else if (browseTarget != null) {
            GeoPoint pos = getUserPosition();
            updateBrowseLeg(pos);
            refreshBrowseNavCard(pos);
        } else {
            loadLieux();
        }
    }

    @SuppressLint("MissingPermission")
    private void startLocationUpdates() {
        // GPS pour précision max, réseau en fallback
        try {
            locationManager.requestLocationUpdates(
                    LocationManager.GPS_PROVIDER, 2000, 5f, this);
        } catch (Exception ignored) {}
        try {
            locationManager.requestLocationUpdates(
                    LocationManager.NETWORK_PROVIDER, 3000, 10f, this);
        } catch (Exception ignored) {}
    }

    // ─── LocationListener ────────────────────────────────────────────────────

    @Override
    public void onLocationChanged(@NonNull Location location) {
        GeoPoint pos = new GeoPoint(location.getLatitude(), location.getLongitude());
        if (!etapes.isEmpty()) {
            updateCurrentLeg(pos);
            refreshNavCard(pos);
            checkAutoAdvance(pos);
        } else if (browseTarget != null) {
            updateBrowseLeg(pos);
            refreshBrowseNavCard(pos);
        }
    }

    // ─── Carte OSMDroid ──────────────────────────────────────────────────────

    private void setupMap() {
        mapView.setTileSource(TileSourceFactory.MAPNIK);
        mapView.setMultiTouchControls(true);
        mapView.getController().setZoom(17.0);
        mapView.getController().setCenter(new GeoPoint(46.8900, -0.9295));
    }

    private void enableLocationOverlay() {
        locationOverlay = new MyLocationNewOverlay(new GpsMyLocationProvider(this), mapView);
        locationOverlay.enableMyLocation();
        mapView.getOverlays().add(locationOverlay);

        locationOverlay.runOnFirstFix(() -> runOnUiThread(() -> {
            compassActive = true;
            if (rotationSensor != null) {
                sensorManager.registerListener(this, rotationSensor, SensorManager.SENSOR_DELAY_GAME);
            }
            centerOnUser();
        }));
    }

    private void centerOnUser() {
        GeoPoint pos = getUserPosition();
        if (pos != null) {
            mapView.getController().animateTo(pos);
        } else {
            mapView.getController().animateTo(new GeoPoint(46.8900, -0.9295));
        }
    }

    private GeoPoint getUserPosition() {
        if (locationOverlay != null) {
            GeoPoint p = locationOverlay.getMyLocation();
            if (p != null) return p;
        }
        return null;
    }

    private void clearMap() {
        mapView.getOverlays().clear();
        if (locationOverlay != null) mapView.getOverlays().add(locationOverlay);
        currentLegPolyline = null;
        browseTarget       = null;
        mapView.invalidate();
        etapes.clear();
        lieux.clear();
        hideLieuInfoCard();
    }

    // ─── Boussole ────────────────────────────────────────────────────────────

    @Override
    public void onSensorChanged(SensorEvent event) {
        if (event.sensor.getType() != Sensor.TYPE_ROTATION_VECTOR) return;
        float[] rotMatrix = new float[9];
        SensorManager.getRotationMatrixFromVector(rotMatrix, event.values);
        float[] orientation = new float[3];
        SensorManager.getOrientation(rotMatrix, orientation);
        float azimuthDeg = (float) Math.toDegrees(orientation[0]);
        azimuthSmoothed  = azimuthSmoothed + 0.08f * (azimuthDeg - azimuthSmoothed);
        mapView.setMapOrientation(-azimuthSmoothed, true);
    }

    @Override public void onAccuracyChanged(Sensor sensor, int accuracy) {}

    // ─── Chargement ──────────────────────────────────────────────────────────

    private void loadCarteData() {
        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/visites/" + idVisite + "/carte", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess()) {
                showEmpty("Erreur " + resp.status);
                return;
            }
            try {
                JSONObject json = new JSONObject(resp.body);
                vitesseMarche = json.optDouble("vitesse_marche", 4.0);

                String nom = json.optString("nom_visite", "");
                if (!nom.isEmpty()) ((androidx.appcompat.widget.Toolbar) findViewById(R.id.toolbar)).setTitle(nom);

                JSONArray arr = json.optJSONArray("etapes");
                etapes.clear();
                if (arr != null) {
                    for (int i = 0; i < arr.length(); i++) {
                        JSONObject o = arr.getJSONObject(i);
                        EtapeGps e   = new EtapeGps();
                        e.ordre       = o.optInt("ordre", i + 1);
                        e.libelle     = o.optString("libelle", "");
                        e.lieuNom     = o.optString("lieu_nom", "");
                        e.lat         = o.optDouble("lat", 0);
                        e.lng         = o.optDouble("lng", 0);
                        e.heureDebut  = o.optString("heure_debut", "");
                        e.heureFin    = o.optString("heure_fin", "");
                        e.heureArrivee = o.optString("heure_arrivee", "");
                        if (e.lat != 0 && e.lng != 0) etapes.add(e);
                    }
                }
                if (etapes.isEmpty()) { showEmpty("Aucune étape GPS pour ce parcours."); return; }

                currentStep = 0;
                drawRoute();

                // Premier refresh avec position connue si dispo
                GeoPoint pos = getUserPosition();
                updateCurrentLeg(pos);
                refreshNavCard(pos);

            } catch (Exception e) {
                showEmpty("Erreur : " + e.getMessage());
            }
        });
    }

    // ─── Dessin de la route entre spectacles ─────────────────────────────────

    private void drawRoute() {
        // Polyligne bleue entre les spectacles
        Polyline route = new Polyline();
        route.setColor(Color.parseColor("#1976D2"));
        route.setWidth(7f);
        List<GeoPoint> pts = new ArrayList<>();
        for (EtapeGps e : etapes) pts.add(new GeoPoint(e.lat, e.lng));
        route.setPoints(pts);
        mapView.getOverlays().add(route);

        // Marqueurs numérotés
        for (int i = 0; i < etapes.size(); i++) {
            EtapeGps e  = etapes.get(i);
            Marker marker = new Marker(mapView);
            marker.setPosition(new GeoPoint(e.lat, e.lng));
            marker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM);
            marker.setTitle((i + 1) + ". " + e.libelle);
            marker.setSnippet(formatHeure(e.heureDebut) + " – " + formatHeure(e.heureFin));

            String color = (i == 0) ? "#1565C0" : (i == etapes.size()-1) ? "#B71C1C" : "#1976D2";
            marker.setIcon(makeMarkerIcon(i + 1, color));

            final int idx = i;
            marker.setOnMarkerClickListener((m, mv) -> {
                currentStep = idx;
                GeoPoint pos = getUserPosition();
                updateCurrentLeg(pos);
                refreshNavCard(pos);
                m.showInfoWindow();
                return true;
            });
            mapView.getOverlays().add(marker);
        }

        mapView.getController().animateTo(new GeoPoint(etapes.get(0).lat, etapes.get(0).lng));
        mapView.invalidate();
    }

    // ─── Trait dynamique user → prochain spectacle ────────────────────────────

    private void updateCurrentLeg(GeoPoint userPos) {
        if (etapes.isEmpty() || userPos == null) return;
        EtapeGps next = etapes.get(Math.min(currentStep, etapes.size() - 1));

        // Supprimer l'ancien trait
        if (currentLegPolyline != null) {
            mapView.getOverlays().remove(currentLegPolyline);
        }

        // Nouveau trait orange de l'utilisateur au prochain spectacle
        currentLegPolyline = new Polyline();
        currentLegPolyline.setColor(Color.parseColor("#FF6D00"));
        currentLegPolyline.setWidth(6f);
        List<GeoPoint> leg = new ArrayList<>();
        leg.add(userPos);
        leg.add(new GeoPoint(next.lat, next.lng));
        currentLegPolyline.setPoints(leg);

        // Insérer juste au-dessus du fond de carte (index 0 = dessous tout)
        mapView.getOverlays().add(1, currentLegPolyline);
        mapView.invalidate();
    }

    // ─── Avancement automatique ───────────────────────────────────────────────

    private void checkAutoAdvance(GeoPoint userPos) {
        if (etapes.isEmpty() || currentStep >= etapes.size() - 1) return;
        EtapeGps current = etapes.get(currentStep);
        double dist = userPos.distanceToAsDouble(new GeoPoint(current.lat, current.lng));
        if (dist < AUTO_ADVANCE_M) {
            currentStep++;
            refreshNavCard(userPos);
        }
    }

    // ─── Bannière de navigation ───────────────────────────────────────────────

    private void refreshNavCard(GeoPoint userPos) {
        if (etapes.isEmpty()) return;
        int idx     = Math.min(currentStep, etapes.size() - 1);
        EtapeGps e  = etapes.get(idx);

        txtNextName.setText(e.libelle);
        txtStep.setText("Étape " + (idx + 1) + "/" + etapes.size());
        txtShowTime.setText("🎭  Spectacle " + formatHeure(e.heureDebut) + " – " + formatHeure(e.heureFin));

        if (userPos != null) {
            GeoPoint dest    = new GeoPoint(e.lat, e.lng);
            double   distM   = userPos.distanceToAsDouble(dest);
            double   vitMps  = vitesseMarche * 1000.0 / 3600.0;
            int      sec     = (int)(distM / vitMps);
            int      minutes = Math.max(1, (int) Math.ceil(sec / 60.0));

            String distStr = distM < 1000
                    ? (int) distM + " m"
                    : String.format(Locale.FRANCE, "%.1f km", distM / 1000.0);

            // Haut : distance + flèche direction
            txtDistance.setText(distStr);
            txtArrow.setText(getBearingArrow(userPos, dest));

            // Bas : temps · distance · ETA
            txtWalkTime.setText(minutes + " min");
            txtWalkDist.setText(distStr);
            Calendar eta = Calendar.getInstance();
            eta.add(Calendar.SECOND, sec);
            txtEta.setText(String.format(Locale.FRANCE, "%02dh%02d",
                    eta.get(Calendar.HOUR_OF_DAY), eta.get(Calendar.MINUTE)));
        } else {
            txtDistance.setText("GPS…");
            txtArrow.setText("↑");
            txtWalkTime.setText("—");
            txtWalkDist.setText("—");
            txtEta.setText("—");
        }
    }

    /** Flèche Unicode selon le bearing user → destination. */
    private String getBearingArrow(GeoPoint from, GeoPoint to) {
        double dLon    = Math.toRadians(to.getLongitude() - from.getLongitude());
        double lat1    = Math.toRadians(from.getLatitude());
        double lat2    = Math.toRadians(to.getLatitude());
        double y       = Math.sin(dLon) * Math.cos(lat2);
        double x       = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
        double bearing = (Math.toDegrees(Math.atan2(y, x)) + 360) % 360;
        if (bearing < 22.5 || bearing >= 337.5) return "↑";
        if (bearing < 67.5)  return "↗";
        if (bearing < 112.5) return "→";
        if (bearing < 157.5) return "↘";
        if (bearing < 202.5) return "↓";
        if (bearing < 247.5) return "↙";
        if (bearing < 292.5) return "←";
        return "↖";
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private android.graphics.drawable.Drawable makeMarkerIcon(int num, String hexColor) {
        int size = (int)(40 * getResources().getDisplayMetrics().density);
        android.graphics.Bitmap bmp = android.graphics.Bitmap.createBitmap(
                size, size, android.graphics.Bitmap.Config.ARGB_8888);
        android.graphics.Canvas c = new android.graphics.Canvas(bmp);
        float r = size / 2f;

        android.graphics.Paint bg = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        bg.setColor(Color.parseColor(hexColor));
        c.drawCircle(r, r, r * 0.85f, bg);

        android.graphics.Paint border = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        border.setColor(Color.WHITE);
        border.setStyle(android.graphics.Paint.Style.STROKE);
        border.setStrokeWidth(size * 0.06f);
        c.drawCircle(r, r, r * 0.85f, border);

        android.graphics.Paint txt = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        txt.setColor(Color.WHITE);
        txt.setTextSize(size * 0.40f);
        txt.setTextAlign(android.graphics.Paint.Align.CENTER);
        txt.setTypeface(android.graphics.Typeface.DEFAULT_BOLD);
        c.drawText(String.valueOf(num), r, r - (txt.descent() + txt.ascent()) / 2f, txt);

        return new android.graphics.drawable.BitmapDrawable(getResources(), bmp);
    }

    /** Annule la navigation en cours et revient au mode exploration (lieux). */
    private void cancelNavigation() {
        if (etapes.isEmpty() && browseTarget == null) {
            // Pas de navigation active → quitter normalement
            finish();
            return;
        }
        clearMap();
        idVisite    = 0;
        currentStep = 0;
        // Masquer les cartes nav, afficher la barre de filtre
        findViewById(R.id.navCardTop).setVisibility(View.GONE);
        findViewById(R.id.navCardBottom).setVisibility(View.GONE);
        if (filterBar != null) filterBar.setVisibility(View.VISIBLE);
        loadLieux();
    }

    private void showEmpty(String msg) {
        txtEmpty.setText(msg);
        txtEmpty.setVisibility(View.VISIBLE);
    }

    private static String formatHeure(String time) {
        if (time == null || time.length() < 5) return "—";
        String[] p = time.split(":");
        return p[0] + "h" + p[1];
    }

    // ─── Mode navigation ponctuel (browse) ───────────────────────────────────

    private void updateBrowseLeg(GeoPoint userPos) {
        if (browseTarget == null || userPos == null) return;
        if (currentLegPolyline != null) mapView.getOverlays().remove(currentLegPolyline);
        currentLegPolyline = new Polyline();
        currentLegPolyline.setColor(Color.parseColor("#FF6D00"));
        currentLegPolyline.setWidth(6f);
        List<GeoPoint> leg = new ArrayList<>();
        leg.add(userPos);
        leg.add(new GeoPoint(browseTarget.lat, browseTarget.lng));
        currentLegPolyline.setPoints(leg);
        mapView.getOverlays().add(1, currentLegPolyline);
        mapView.invalidate();
    }

    private void refreshBrowseNavCard(GeoPoint userPos) {
        if (browseTarget == null) return;
        txtNextName.setText(browseTarget.nom);
        txtStep.setText(browseTarget.typeLabel());
        txtShowTime.setText("");

        if (userPos != null) {
            GeoPoint dest   = new GeoPoint(browseTarget.lat, browseTarget.lng);
            double   distM  = userPos.distanceToAsDouble(dest);
            double   vitMps = vitesseMarche * 1000.0 / 3600.0;
            int      sec    = (int)(distM / vitMps);
            int      min    = Math.max(1, (int) Math.ceil(sec / 60.0));

            String distStr = distM < 1000
                    ? (int) distM + " m"
                    : String.format(Locale.FRANCE, "%.1f km", distM / 1000.0);

            txtDistance.setText(distStr);
            txtArrow.setText(getBearingArrow(userPos, dest));
            txtWalkTime.setText(min + " min");
            txtWalkDist.setText(distStr);
            Calendar eta = Calendar.getInstance();
            eta.add(Calendar.SECOND, sec);
            txtEta.setText(String.format(Locale.FRANCE, "%02dh%02d",
                    eta.get(Calendar.HOUR_OF_DAY), eta.get(Calendar.MINUTE)));
        } else {
            txtDistance.setText("GPS…");
            txtArrow.setText("↑");
            txtWalkTime.setText("—");
            txtWalkDist.setText("—");
            txtEta.setText("—");
        }
    }

    // ─── Chargement des lieux (mode exploration) ──────────────────────────────

    private void loadLieux() {
        vitesseMarche = new Session(this).getVitesse();
        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/lieux", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess()) {
                showEmpty("Impossible de charger les lieux (" + resp.status + ")");
                return;
            }
            try {
                JSONArray arr = new JSONArray(resp.body.trim());
                lieux.clear();
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    String gps   = o.optString("coordonnees_gps", "");
                    String[] parts = gps.split(",");
                    if (parts.length < 2) continue;
                    LieuPoint p  = new LieuPoint();
                    p.id         = o.optInt("id_lieu");
                    p.nom        = o.optString("nom", "Lieu #" + p.id);
                    p.typeLieu   = o.optString("type_lieu", "zone");
                    p.lat        = Double.parseDouble(parts[0].trim());
                    p.lng        = Double.parseDouble(parts[1].trim());
                    lieux.add(p);
                }
                if (lieux.isEmpty()) { showEmpty("Aucun lieu disponible."); return; }
                drawLieuxMarkers();
            } catch (Exception e) {
                showEmpty("Erreur : " + e.getMessage());
            }
        });
    }

    private void drawLieuxMarkers() {
        // Tous les types actifs par défaut (reset au chargement)
        activeFilters.clear();
        activeFilters.addAll(Arrays.asList("spectacle","restaurant","hotel","accueil","allee","zone"));
        for (String[] def : TYPE_DEFS) {
            Button chip = filterChips.get(def[0]);
            if (chip != null) styleChipActive(chip, def[1], def[2]);
        }

        applyFilter();
        mapView.getController().animateTo(new GeoPoint(46.8900, -0.9295));
        mapView.getController().setZoom(16.0);
    }

    // ─── Popup info lieu ─────────────────────────────────────────────────────

    // ─── Barre de filtres (légende + sélection) ───────────────────────────────

    private static final String[][] TYPE_DEFS = {
        {"spectacle", "Spectacle",  "#C62828"},
        {"restaurant","Restaurant", "#E65100"},
        {"hotel",     "Hôtel",      "#1565C0"},
        {"accueil",   "Accueil",    "#2E7D32"},
        {"allee",     "Allée",      "#4527A0"},
        {"zone",      "Zone",       "#37474F"},
    };

    private void buildFilterBar() {
        FrameLayout mapFrame = (FrameLayout) mapView.getParent();
        float dp = getResources().getDisplayMetrics().density;

        filterBar = new HorizontalScrollView(this);
        filterBar.setHorizontalScrollBarEnabled(false);
        filterBar.setBackgroundColor(Color.TRANSPARENT);

        LinearLayout row = new LinearLayout(this);
        row.setOrientation(LinearLayout.HORIZONTAL);
        row.setGravity(android.view.Gravity.CENTER_VERTICAL);
        row.setPadding((int)(12*dp), (int)(10*dp), (int)(12*dp), (int)(10*dp));

        for (String[] def : TYPE_DEFS) {
            String key   = def[0];
            String label = def[1];
            String color = def[2];

            Button chip = new Button(this);
            chip.setAllCaps(false);
            chip.setTextSize(12f);
            chip.setTypeface(null, android.graphics.Typeface.BOLD);
            chip.setIncludeFontPadding(false);
            chip.setPadding((int)(14*dp), (int)(6*dp), (int)(14*dp), (int)(6*dp));
            chip.setStateListAnimator(null); // supprime l'élévation Material

            LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.WRAP_CONTENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT);
            lp.setMargins((int)(4*dp), 0, (int)(4*dp), 0);
            chip.setLayoutParams(lp);

            styleChipActive(chip, label, color);

            chip.setOnClickListener(v -> {
                if (activeFilters.contains(key)) {
                    activeFilters.remove(key);
                    styleChipInactive(chip, label, color);
                } else {
                    activeFilters.add(key);
                    styleChipActive(chip, label, color);
                }
                applyFilter();
            });

            filterChips.put(key, chip);
            row.addView(chip);
        }

        filterBar.addView(row);

        FrameLayout.LayoutParams flp = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        flp.gravity = android.view.Gravity.TOP;
        mapFrame.addView(filterBar, flp);
    }

    /** Chip actif : fond coloré plein, texte blanc, pill arrondi. */
    private void styleChipActive(Button chip, String label, String hexColor) {
        chip.setText(label);
        chip.setTextColor(Color.WHITE);
        chip.setBackground(makeChipDrawable(hexColor, true));
    }

    /** Chip inactif : fond transparent, bordure + texte colorés, pill arrondi. */
    private void styleChipInactive(Button chip, String label, String hexColor) {
        chip.setText(label);
        chip.setTextColor(Color.parseColor(hexColor));
        chip.setBackground(makeChipDrawable(hexColor, false));
    }

    private android.graphics.drawable.GradientDrawable makeChipDrawable(String hexColor, boolean filled) {
        android.graphics.drawable.GradientDrawable d = new android.graphics.drawable.GradientDrawable();
        d.setShape(android.graphics.drawable.GradientDrawable.RECTANGLE);
        d.setCornerRadius(999f); // pill
        if (filled) {
            d.setColor(Color.parseColor(hexColor));
            d.setStroke(0, Color.TRANSPARENT);
        } else {
            d.setColor(Color.parseColor("#22FFFFFF")); // quasi-transparent
            d.setStroke((int)(1.5f * getResources().getDisplayMetrics().density),
                    Color.parseColor(hexColor));
        }
        return d;
    }

    private void applyFilter() {
        // Supprimer tous les marqueurs de lieux existants (pas la localisation ni la polyline)
        List<org.osmdroid.views.overlay.Overlay> toRemove = new ArrayList<>();
        for (org.osmdroid.views.overlay.Overlay o : mapView.getOverlays()) {
            if (o instanceof Marker) toRemove.add(o);
        }
        mapView.getOverlays().removeAll(toRemove);

        // Redessiner seulement les types actifs
        for (LieuPoint p : lieux) {
            if (!activeFilters.contains(p.typeLieu)) continue;
            Marker marker = new Marker(mapView);
            marker.setPosition(new GeoPoint(p.lat, p.lng));
            marker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_CENTER);
            marker.setIcon(makeLieuIcon(p.typeColor()));
            marker.setTitle(p.nom);
            marker.setOnMarkerClickListener((m, mv) -> {
                hideLieuInfoCard();
                showLieuInfoCard(p);
                return true;
            });
            mapView.getOverlays().add(marker);
        }
        mapView.invalidate();
    }

    /** Construit la carte info lieu et l'ajoute (invisible) au FrameLayout de la carte. */
    private void buildLieuInfoCard() {
        FrameLayout mapFrame = (FrameLayout) mapView.getParent();

        lieuInfoCard = new LinearLayout(this);
        lieuInfoCard.setOrientation(LinearLayout.VERTICAL);
        lieuInfoCard.setBackgroundColor(Color.WHITE);
        lieuInfoCard.setVisibility(View.GONE);

        int pad = (int)(16 * getResources().getDisplayMetrics().density);
        lieuInfoCard.setPadding(pad, pad, pad, pad);

        // Elevation (shadow)
        lieuInfoCard.setElevation(12 * getResources().getDisplayMetrics().density);

        FrameLayout.LayoutParams lp = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        // Positioned just above bottom nav — bottom nav is ~56dp, plus small margin
        lp.gravity = Gravity.BOTTOM;
        lp.bottomMargin = (int)(56 * getResources().getDisplayMetrics().density);
        mapFrame.addView(lieuInfoCard, lp);
    }

    private void showLieuInfoCard(LieuPoint p) {
        lieuInfoCard.removeAllViews();
        float dp = getResources().getDisplayMetrics().density;

        // Type badge
        TextView tvType = new TextView(this);
        tvType.setText(p.typeLabel().toUpperCase());
        tvType.setTextSize(11);
        tvType.setTextColor(Color.parseColor(p.typeColor()));
        tvType.setTypeface(null, android.graphics.Typeface.BOLD);
        lieuInfoCard.addView(tvType);

        // Nom
        TextView tvNom = new TextView(this);
        tvNom.setText(p.nom);
        tvNom.setTextSize(18);
        tvNom.setTextColor(Color.parseColor("#1A1A2E"));
        tvNom.setTypeface(null, android.graphics.Typeface.BOLD);
        LinearLayout.LayoutParams nomLp = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        nomLp.setMargins(0, (int)(2*dp), 0, (int)(10*dp));
        tvNom.setLayoutParams(nomLp);
        lieuInfoCard.addView(tvNom);

        // Boutons côte à côte
        LinearLayout btnRow = new LinearLayout(this);
        btnRow.setOrientation(LinearLayout.HORIZONTAL);

        Button btnYAller = new Button(this);
        btnYAller.setText("Y aller");
        btnYAller.setBackgroundColor(Color.parseColor(p.typeColor()));
        btnYAller.setTextColor(Color.WHITE);
        btnYAller.setAllCaps(false);
        LinearLayout.LayoutParams btnLp = new LinearLayout.LayoutParams(
                0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f);
        btnLp.setMargins(0, 0, (int)(8*dp), 0);
        btnYAller.setLayoutParams(btnLp);
        btnYAller.setOnClickListener(v -> {
            browseTarget = p;
            hideLieuInfoCard();
            // Afficher les cartes de navigation
            findViewById(R.id.navCardTop).setVisibility(View.VISIBLE);
            findViewById(R.id.navCardBottom).setVisibility(View.VISIBLE);
            GeoPoint userPos = getUserPosition();
            updateBrowseLeg(userPos);
            refreshBrowseNavCard(userPos);
            mapView.getController().animateTo(new GeoPoint(p.lat, p.lng));
        });
        btnRow.addView(btnYAller);

        Button btnFermer = new Button(this);
        btnFermer.setText("Fermer");
        btnFermer.setBackgroundColor(Color.parseColor("#EEEEEE"));
        btnFermer.setTextColor(Color.parseColor("#555555"));
        btnFermer.setAllCaps(false);
        LinearLayout.LayoutParams closeLp = new LinearLayout.LayoutParams(
                0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f);
        btnFermer.setLayoutParams(closeLp);
        btnFermer.setOnClickListener(v -> hideLieuInfoCard());
        btnRow.addView(btnFermer);

        lieuInfoCard.addView(btnRow);
        lieuInfoCard.setVisibility(View.VISIBLE);
    }

    private void hideLieuInfoCard() {
        if (lieuInfoCard != null) lieuInfoCard.setVisibility(View.GONE);
    }

    // ─── Icône lieu (cercle coloré sans numéro) ───────────────────────────────

    private android.graphics.drawable.Drawable makeLieuIcon(String hexColor) {
        int size = (int)(32 * getResources().getDisplayMetrics().density);
        android.graphics.Bitmap bmp = android.graphics.Bitmap.createBitmap(
                size, size, android.graphics.Bitmap.Config.ARGB_8888);
        android.graphics.Canvas c = new android.graphics.Canvas(bmp);
        float r = size / 2f;

        android.graphics.Paint bg = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        bg.setColor(Color.parseColor(hexColor));
        c.drawCircle(r, r, r * 0.75f, bg);

        android.graphics.Paint border = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        border.setColor(Color.WHITE);
        border.setStyle(android.graphics.Paint.Style.STROKE);
        border.setStrokeWidth(size * 0.10f);
        c.drawCircle(r, r, r * 0.75f, border);

        return new android.graphics.drawable.BitmapDrawable(getResources(), bmp);
    }
}
