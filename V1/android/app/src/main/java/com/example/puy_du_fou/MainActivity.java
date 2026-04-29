package com.example.puy_du_fou;

import android.content.Intent;
import android.graphics.drawable.GradientDrawable;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.adapter.RecentAdapter;
import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.NavHelper;
import com.example.puy_du_fou.util.RecentlyViewed;
import com.example.puy_du_fou.util.Session;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.card.MaterialCardView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;

public class MainActivity extends AppCompatActivity {

    private ApiClient api;
    private LinearLayout seancesContainer;
    private TextView emptySeances;
    private TextView statsVisites;

    @Override
    protected void onCreate(Bundle savedInstanceState) { 
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Session session = new Session(this);
        if (!session.isLoggedIn()) {
            startActivity(new Intent(this, LoginActivity.class));
            finish();
            return;
        }

        api = new ApiClient(this);

        // Navbar
        BottomNavigationView nav = findViewById(R.id.bottomNav);
        NavHelper.setup(this, nav, R.id.nav_home);

        // Welcome + date
        ((TextView) findViewById(R.id.welcomeText))
                .setText("Bienvenue " + session.getPrenom() + " !");
        ((TextView) findViewById(R.id.dateText))
                .setText(getTodayFormatted());

        seancesContainer = findViewById(R.id.seancesContainer);
        emptySeances     = findViewById(R.id.emptySeances);
        statsVisites     = findViewById(R.id.statsVisites);

        // Actions rapides
        ((MaterialCardView) findViewById(R.id.cardPlanifier))
                .setOnClickListener(v -> startActivity(new Intent(this, SpectaclesActivity.class)));
        ((MaterialCardView) findViewById(R.id.cardCatalogue))
                .setOnClickListener(v -> navigateTo(CatalogueActivity.class));
        ((MaterialCardView) findViewById(R.id.cardHistorique))
                .setOnClickListener(v -> navigateTo(HistoriqueActivity.class));
        findViewById(R.id.btnVoirHistorique)
                .setOnClickListener(v -> navigateTo(HistoriqueActivity.class));

        loadDashboard();
    }

    @Override
    protected void onResume() {
        super.onResume();
        NavHelper.syncSelection(this, R.id.nav_home);
        // Rafraîchit les vus récemment à chaque retour sur l'accueil
        setupRecentlyViewed();
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
    }

    // ─── Données ────────────────────────────────────────────────────────────

    private void loadDashboard() {
        loadParkStatus();
        loadVisitesCount();
    }

    /** Charge les jours d'ouverture → statut du parc + prochain jour → séances */
    private void loadParkStatus() {
        api.get("/api/jours", resp -> {
            if (!resp.isSuccess() || resp.body == null) {
                setStatusFerme("Statut indisponible", "");
                return;
            }
            try {
                JSONArray jours = new JSONArray(resp.body);
                String today = getTodayKey();
                String ouvertureAujourdHui = null;
                String fermetureAujourdHui = null;
                String prochainJour = null;
                String prochainJourLabel = null;

                for (int i = 0; i < jours.length(); i++) {
                    JSONObject j = jours.getJSONObject(i);
                    String date = j.optString("id_jours");
                    if (date.compareTo(today) < 0) continue; // passé

                    if (date.equals(today)) {
                        ouvertureAujourdHui = j.optString("heure_ouverture");
                        fermetureAujourdHui = j.optString("heure_fermeture");
                    }
                    if (prochainJour == null && date.compareTo(today) >= 0) {
                        prochainJour      = date;
                        prochainJourLabel = formatDate(date);
                    }
                }

                if (ouvertureAujourdHui != null) {
                    String heures = formatHeure(ouvertureAujourdHui)
                            + " – " + formatHeure(fermetureAujourdHui);
                    setStatusOuvert("Ouvert aujourd'hui", heures);
                } else {
                    setStatusFerme("Fermé aujourd'hui", prochainJour != null
                            ? "Prochain : " + prochainJourLabel : "");
                }

                if (prochainJour != null) {
                    final String dateSeances  = prochainJour;
                    final String labelSeances = prochainJourLabel;
                    loadSeances(dateSeances, labelSeances);
                } else {
                    emptySeances.setVisibility(View.VISIBLE);
                }

            } catch (Exception e) {
                setStatusFerme("Statut indisponible", "");
                emptySeances.setVisibility(View.VISIBLE);
            }
        });
    }

    /** Charge les séances du prochain jour ouvert */
    private void loadSeances(String date, String dateLabel) {
        ((TextView) findViewById(R.id.dateSeancesText)).setText(dateLabel);

        api.get("/api/seances?date=" + date, resp -> {
            if (!resp.isSuccess() || resp.body == null) {
                emptySeances.setVisibility(View.VISIBLE);
                return;
            }
            try {
                JSONArray arr = new JSONArray(resp.body);
                seancesContainer.removeAllViews();

                int count = Math.min(arr.length(), 7); // max 7 lignes
                if (count == 0) {
                    emptySeances.setVisibility(View.VISIBLE);
                    return;
                }

                LayoutInflater inflater = LayoutInflater.from(this);
                for (int i = 0; i < count; i++) {
                    JSONObject s = arr.getJSONObject(i);
                    View row = inflater.inflate(R.layout.item_dash_seance, seancesContainer, false);

                    ((TextView) row.findViewById(R.id.seanceHeure))
                            .setText(formatHeure(s.optString("heure_debut")));
                    ((TextView) row.findViewById(R.id.seanceLibelle))
                            .setText(s.optString("libelle"));
                    ((TextView) row.findViewById(R.id.seanceFinHeure))
                            .setText(formatHeure(s.optString("heure_fin")));

                    // Séparateur entre lignes
                    if (i < count - 1) {
                        View sep = new View(this);
                        LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                                LinearLayout.LayoutParams.MATCH_PARENT, 1);
                        sep.setBackgroundColor(0xFFEEEEEE);
                        seancesContainer.addView(row);
                        seancesContainer.addView(sep, lp);
                    } else {
                        seancesContainer.addView(row);
                    }
                }

                // Lien "Voir plus" si >7 séances
                if (arr.length() > 7) {
                    TextView voirPlus = new TextView(this);
                    voirPlus.setText("Voir les " + arr.length() + " spectacles →");
                    voirPlus.setTextColor(getResources().getColor(R.color.colorPrimary, getTheme()));
                    voirPlus.setTextSize(13);
                    voirPlus.setPadding(0, 12, 0, 8);
                    voirPlus.setOnClickListener(v -> navigateTo(CatalogueActivity.class));
                    seancesContainer.addView(voirPlus);
                }

            } catch (Exception e) {
                emptySeances.setVisibility(View.VISIBLE);
            }
        });
    }

    /** Charge le nombre de visites de l'utilisateur */
    private void loadVisitesCount() {
        api.get("/api/visites", resp -> {
            if (!resp.isSuccess() || resp.body == null) return;
            try {
                int count = new JSONArray(resp.body).length();
                statsVisites.setText(count == 0
                        ? "Aucune visite planifiée"
                        : count + " visite" + (count > 1 ? "s" : "") + " planifiée" + (count > 1 ? "s" : ""));
            } catch (Exception ignored) {}
        });
    }

    /** Affiche les spectacles récemment consultés */
    private void setupRecentlyViewed() {
        List<RecentlyViewed.Item> recent = RecentlyViewed.load(this);
        LinearLayout section = findViewById(R.id.sectionRecent);

        if (recent.isEmpty()) {
            section.setVisibility(View.GONE);
            return;
        }

        section.setVisibility(View.VISIBLE);
        RecyclerView rv = findViewById(R.id.recyclerRecent);
        rv.setLayoutManager(new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false));
        rv.setAdapter(new RecentAdapter(recent, item -> {
            Intent i = new Intent(this, SpectacleDetailActivity.class);
            i.putExtra("id_spectacle", item.id);
            i.putExtra("libelle", item.libelle);
            i.putExtra("description", item.description);
            i.putExtra("duree", item.duree);
            i.putExtra("attente", item.attente);
            i.putExtra("lieu", item.lieu);
            startActivity(i);
        }));
    }

    // ─── Statut du parc ─────────────────────────────────────────────────────

    private void setStatusOuvert(String label, String heures) {
        setStatus(label, heures, 0xFF4CAF50, "#E8F5E9"); // vert
    }

    private void setStatusFerme(String label, String heures) {
        setStatus(label, heures, 0xFF9E9E9E, "#F5F5F5"); // gris
    }

    private void setStatus(String label, String heures, int dotColor, String bgHex) {
        LinearLayout bar = findViewById(R.id.statusBar);
        bar.setBackgroundColor(android.graphics.Color.parseColor(bgHex));

        GradientDrawable dot = new GradientDrawable();
        dot.setShape(GradientDrawable.OVAL);
        dot.setColor(dotColor);
        findViewById(R.id.statusDot).setBackground(dot);

        ((TextView) findViewById(R.id.statusText)).setText(label);
        ((TextView) findViewById(R.id.statusHours)).setText(heures);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private void navigateTo(Class<?> target) {
        Intent intent = new Intent(this, target);
        intent.addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
        startActivity(intent);
        overridePendingTransition(0, 0);
    }

    private String getTodayKey() {
        return new SimpleDateFormat("yyyy-MM-dd", Locale.US).format(new Date());
    }

    private String getTodayFormatted() {
        return new SimpleDateFormat("EEEE d MMMM yyyy", Locale.FRANCE).format(new Date());
    }

    private String formatDate(String isoDate) {
        try {
            Date d = new SimpleDateFormat("yyyy-MM-dd", Locale.FRANCE).parse(isoDate);
            return new SimpleDateFormat("EEEE d MMMM", Locale.FRANCE).format(d);
        } catch (Exception e) {
            return isoDate;
        }
    }

    /** "09:30:00" → "09h30" */
    private String formatHeure(String time) {
        if (time == null || time.length() < 5) return time;
        String hm = time.substring(0, 5); // "09:30"
        return hm.replace(":", "h");      // "09h30"
    }
}
