package com.example.puy_du_fou;

import android.Manifest;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.hardware.SensorManager;
import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.NavHelper;
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
import java.util.List;

public class CarteActivity extends AppCompatActivity implements SensorEventListener {

    private static final int REQ_LOCATION = 1001;

    // ─── Modèle ─────────────────────────────────────────────────────────────

    static class EtapeGps {
        int    ordre;
        String libelle;
        String lieuNom;
        double lat, lng;
        String heureDebut;   // "HH:MM:SS"
        String heureFin;
        String heureArrivee;
    }

    // ─── Vues ────────────────────────────────────────────────────────────────

    private MapView mapView;
    private MyLocationNewOverlay locationOverlay;
    private ProgressBar progress;
    private TextView txtEmpty, txtNextName, txtWalkTime, txtShowTime, txtStep;
    private FloatingActionButton btnCenter;

    // ─── Données ─────────────────────────────────────────────────────────────

    private final List<EtapeGps> etapes     = new ArrayList<>();
    private double vitesseMarche            = 4.0; // km/h
    private int    idVisite                 = 0;

    // ─── Capteurs ────────────────────────────────────────────────────────────

    private SensorManager sensorManager;
    private Sensor        rotationSensor;
    private float         azimuthSmoothed   = 0f;
    private boolean       compassActive     = false;

    // ─── Lifecycle ───────────────────────────────────────────────────────────

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // OSMDroid : configuration avant setContentView
        Configuration.getInstance().load(this,
                getSharedPreferences("osmdroid", MODE_PRIVATE));
        Configuration.getInstance().setUserAgentValue(getPackageName());

        setContentView(R.layout.activity_carte);

        // Vues
        mapView      = findViewById(R.id.map);
        progress     = findViewById(R.id.progress);
        txtEmpty     = findViewById(R.id.txtEmpty);
        txtNextName  = findViewById(R.id.txtNextName);
        txtWalkTime  = findViewById(R.id.txtWalkTime);
        txtShowTime  = findViewById(R.id.txtShowTime);
        txtStep      = findViewById(R.id.txtStep);
        btnCenter    = findViewById(R.id.btnCenter);

        // Toolbar
        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        setSupportActionBar(tb);
        tb.setNavigationOnClickListener(v -> finish());

        // Navbar
        NavHelper.setup(this, (BottomNavigationView) findViewById(R.id.bottomNav),
                R.id.nav_carte);

        // Paramètre : visite à afficher (optionnel — si absent, attend un choix)
        idVisite = getIntent().getIntExtra("id_visite", 0);

        // OSMDroid map setup
        setupMap();

        // Capteurs boussole
        sensorManager  = (SensorManager) getSystemService(SENSOR_SERVICE);
        rotationSensor = sensorManager.getDefaultSensor(Sensor.TYPE_ROTATION_VECTOR);

        // GPS permission + chargement
        if (hasLocationPermission()) {
            enableLocationOverlay();
            if (idVisite > 0) loadCarteData();
            else showSelectVisiteHint();
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
            sensorManager.registerListener(this, rotationSensor,
                    SensorManager.SENSOR_DELAY_GAME);
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        mapView.onPause();
        sensorManager.unregisterListener(this);
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        int newId = intent.getIntExtra("id_visite", 0);
        if (newId > 0 && newId != idVisite) {
            idVisite = newId;
            clearMap();
            loadCarteData();
        }
    }

    // ─── Permissions ─────────────────────────────────────────────────────────

    private boolean hasLocationPermission() {
        return ContextCompat.checkSelfPermission(this,
                Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED;
    }

    @Override
    public void onRequestPermissionsResult(int req, @NonNull String[] perms,
                                           @NonNull int[] results) {
        super.onRequestPermissionsResult(req, perms, results);
        if (req == REQ_LOCATION && results.length > 0
                && results[0] == PackageManager.PERMISSION_GRANTED) {
            enableLocationOverlay();
            if (idVisite > 0) loadCarteData();
            else showSelectVisiteHint();
        } else {
            Toast.makeText(this, "Permission GPS refusée — position désactivée",
                    Toast.LENGTH_LONG).show();
            if (idVisite > 0) loadCarteData();
            else showSelectVisiteHint();
        }
    }

    // ─── Carte OSMDroid ──────────────────────────────────────────────────────

    private void setupMap() {
        mapView.setTileSource(TileSourceFactory.MAPNIK);
        mapView.setMultiTouchControls(true);
        mapView.getController().setZoom(17.0);
        // Centre par défaut sur le parc Puy du Fou
        mapView.getController().setCenter(new GeoPoint(46.8900, -0.9295));
    }

    private void enableLocationOverlay() {
        locationOverlay = new MyLocationNewOverlay(
                new GpsMyLocationProvider(this), mapView);
        locationOverlay.enableMyLocation();
        mapView.getOverlays().add(locationOverlay);

        // Active la boussole une fois que la position est connue
        locationOverlay.runOnFirstFix(() -> runOnUiThread(() -> {
            compassActive = true;
            if (rotationSensor != null) {
                sensorManager.registerListener(CarteActivity.this, rotationSensor,
                        SensorManager.SENSOR_DELAY_GAME);
            }
            centerOnUser();
        }));
    }

    private void centerOnUser() {
        if (locationOverlay != null) {
            GeoPoint pos = locationOverlay.getMyLocation();
            if (pos != null) {
                mapView.getController().animateTo(pos);
                return;
            }
        }
        // Fallback : centre sur le parc
        mapView.getController().animateTo(new GeoPoint(46.8900, -0.9295));
    }

    private void clearMap() {
        mapView.getOverlays().clear();
        if (locationOverlay != null) mapView.getOverlays().add(locationOverlay);
        mapView.invalidate();
        etapes.clear();
    }

    // ─── Boussole (rotation de la carte) ─────────────────────────────────────

    @Override
    public void onSensorChanged(SensorEvent event) {
        if (event.sensor.getType() != Sensor.TYPE_ROTATION_VECTOR) return;

        float[] rotMatrix = new float[9];
        SensorManager.getRotationMatrixFromVector(rotMatrix, event.values);

        float[] orientation = new float[3];
        SensorManager.getOrientation(rotMatrix, orientation);

        float azimuthDeg = (float) Math.toDegrees(orientation[0]);

        // Lissage exponentiel pour éviter les à-coups
        float alpha = 0.08f;
        azimuthSmoothed = azimuthSmoothed + alpha * (azimuthDeg - azimuthSmoothed);

        // Rotation de la carte : -azimuth pour que le nord soit toujours vers le haut
        // quand l'utilisateur regarde vers le nord, et que la carte "tourne" avec lui
        mapView.setMapOrientation(-azimuthSmoothed, true);
    }

    @Override
    public void onAccuracyChanged(Sensor sensor, int accuracy) { /* ignoré */ }

    // ─── Chargement des données ───────────────────────────────────────────────

    private void loadCarteData() {
        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/visites/" + idVisite + "/carte", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess()) {
                String msg = resp.status == 0
                        ? "Serveur inaccessible"
                        : "Erreur " + resp.status;
                showEmpty(msg);
                return;
            }
            try {
                JSONObject json = new JSONObject(resp.body);
                vitesseMarche = json.optDouble("vitesse_marche", 4.0);

                String nomVisite = json.optString("nom_visite", "");
                if (!nomVisite.isEmpty()) {
                    androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
                    tb.setTitle(nomVisite);
                }

                JSONArray arr = json.optJSONArray("etapes");
                etapes.clear();
                if (arr != null) {
                    for (int i = 0; i < arr.length(); i++) {
                        JSONObject o = arr.getJSONObject(i);
                        EtapeGps e  = new EtapeGps();
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

                if (etapes.isEmpty()) {
                    showEmpty("Aucune étape GPS disponible pour ce parcours.");
                    return;
                }

                drawRoute();
                updateNavCard(0);

            } catch (Exception e) {
                showEmpty("Erreur de lecture : " + e.getMessage());
            }
        });
    }

    // ─── Dessin de la route ───────────────────────────────────────────────────

    private void drawRoute() {
        // Polyligne reliant les étapes dans l'ordre
        Polyline polyline = new Polyline();
        polyline.setColor(Color.parseColor("#1976D2"));
        polyline.setWidth(8f);

        List<GeoPoint> points = new ArrayList<>();
        for (EtapeGps e : etapes) {
            points.add(new GeoPoint(e.lat, e.lng));
        }
        polyline.setPoints(points);
        mapView.getOverlays().add(polyline);

        // Marqueurs numérotés pour chaque étape
        for (int i = 0; i < etapes.size(); i++) {
            EtapeGps e = etapes.get(i);
            Marker marker = new Marker(mapView);
            marker.setPosition(new GeoPoint(e.lat, e.lng));
            marker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM);
            marker.setTitle((i + 1) + ". " + e.libelle);
            marker.setSnippet(formatHeure(e.heureDebut) + " – " + formatHeure(e.heureFin));

            // Couleur : premier = bleu foncé, dernier = rouge, autres = bleu
            if (i == 0)                  marker.setIcon(makeMarkerIcon(i + 1, "#1565C0"));
            else if (i == etapes.size() - 1) marker.setIcon(makeMarkerIcon(i + 1, "#B71C1C"));
            else                         marker.setIcon(makeMarkerIcon(i + 1, "#1976D2"));

            final int idx = i;
            marker.setOnMarkerClickListener((m, mv) -> {
                updateNavCard(idx);
                m.showInfoWindow();
                return true;
            });
            mapView.getOverlays().add(marker);
        }

        // Centrer la carte sur la première étape
        mapView.getController().animateTo(new GeoPoint(etapes.get(0).lat, etapes.get(0).lng));
        mapView.invalidate();
    }

    /**
     * Crée une icône de marqueur numéroté avec la couleur donnée.
     * Utilise un MarkerInfoWindow coloré en bitmap.
     */
    private android.graphics.drawable.Drawable makeMarkerIcon(int num, String hexColor) {
        // On génère un bitmap avec le numéro dessiné dessus
        int size = (int)(40 * getResources().getDisplayMetrics().density);
        android.graphics.Bitmap bmp = android.graphics.Bitmap.createBitmap(size, size,
                android.graphics.Bitmap.Config.ARGB_8888);
        android.graphics.Canvas c = new android.graphics.Canvas(bmp);

        // Cercle de fond
        android.graphics.Paint bgPaint = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        bgPaint.setColor(Color.parseColor(hexColor));
        float r = size / 2f;
        c.drawCircle(r, r, r * 0.85f, bgPaint);

        // Bordure blanche
        android.graphics.Paint borderPaint = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        borderPaint.setColor(Color.WHITE);
        borderPaint.setStyle(android.graphics.Paint.Style.STROKE);
        borderPaint.setStrokeWidth(size * 0.06f);
        c.drawCircle(r, r, r * 0.85f, borderPaint);

        // Numéro
        android.graphics.Paint textPaint = new android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG);
        textPaint.setColor(Color.WHITE);
        textPaint.setTextSize(size * 0.40f);
        textPaint.setTextAlign(android.graphics.Paint.Align.CENTER);
        textPaint.setTypeface(android.graphics.Typeface.DEFAULT_BOLD);
        float textY = r - (textPaint.descent() + textPaint.ascent()) / 2f;
        c.drawText(String.valueOf(num), r, textY, textPaint);

        return new android.graphics.drawable.BitmapDrawable(getResources(), bmp);
    }

    // ─── Bannière de navigation bas ───────────────────────────────────────────

    /**
     * Met à jour la carte de navigation avec les infos de l'étape `index`.
     * Calcule le temps de marche depuis la position GPS actuelle (ou depuis
     * l'étape précédente si GPS indisponible).
     */
    private void updateNavCard(int index) {
        if (etapes.isEmpty()) return;
        int safeIndex = Math.max(0, Math.min(index, etapes.size() - 1));
        EtapeGps next = etapes.get(safeIndex);

        txtNextName.setText(next.libelle);
        txtShowTime.setText("Spectacle à " + formatHeure(next.heureDebut));
        txtStep.setText((safeIndex + 1) + " / " + etapes.size());

        // Calcul du temps de marche jusqu'au prochain spectacle
        double distM = getDistanceToEtape(next);
        if (distM >= 0) {
            int minutes = (int) Math.ceil(distM / (vitesseMarche * 1000.0 / 60.0));
            txtWalkTime.setText("~" + minutes + " min à pied");
        } else {
            txtWalkTime.setText("—");
        }
    }

    /**
     * Distance en mètres entre la position GPS actuelle et une étape.
     * Retourne -1 si la position est inconnue.
     */
    private double getDistanceToEtape(EtapeGps etape) {
        if (locationOverlay == null) return -1;
        GeoPoint myPos = locationOverlay.getMyLocation();
        if (myPos == null) return -1;
        return myPos.distanceToAsDouble(new GeoPoint(etape.lat, etape.lng));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private void showEmpty(String msg) {
        txtEmpty.setText(msg);
        txtEmpty.setVisibility(View.VISIBLE);
    }

    private void showSelectVisiteHint() {
        showEmpty("Ouvrez une visite depuis l'Historique\npuis appuyez sur « Voir sur la carte ».");
    }

    /** Formate "14:30:00" en "14h30". */
    private static String formatHeure(String time) {
        if (time == null || time.length() < 5) return "—";
        String[] parts = time.split(":");
        return parts[0] + "h" + parts[1];
    }
}
