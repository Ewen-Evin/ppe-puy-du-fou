package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.api.ApiClient;

import org.json.JSONArray;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class SpectacleDetailActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_spectacle_detail);

        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        tb.setNavigationOnClickListener(v -> finish());

        int id          = getIntent().getIntExtra("id_spectacle", 0);
        String libelle  = getIntent().getStringExtra("libelle");
        String desc     = getIntent().getStringExtra("description");
        String duree    = getIntent().getStringExtra("duree");
        String attente  = getIntent().getStringExtra("attente");
        String lieu     = getIntent().getStringExtra("lieu");
        String gps      = getIntent().getStringExtra("coordonnees_gps");

        ((TextView) findViewById(R.id.titre)).setText(libelle);
        ((TextView) findViewById(R.id.lieu)).setText("📍 " + (lieu != null ? lieu : ""));

        // Bouton "Y aller" → CarteActivity avec ce lieu comme destination
        Button btnYAller = findViewById(R.id.btnYAller);
        if (gps != null && !gps.isEmpty()) {
            btnYAller.setOnClickListener(v -> {
                String[] parts = gps.split(",");
                if (parts.length == 2) {
                    Intent it = new Intent(this, CarteActivity.class);
                    it.putExtra("dest_lat",  Double.parseDouble(parts[0].trim()));
                    it.putExtra("dest_lng",  Double.parseDouble(parts[1].trim()));
                    it.putExtra("dest_nom",  lieu != null ? lieu : libelle);
                    it.putExtra("dest_type", "spectacle");
                    startActivity(it);
                }
            });
        } else {
            btnYAller.setVisibility(android.view.View.GONE);
        }
        ((TextView) findViewById(R.id.duree)).setText("⏱ Durée : " + (duree != null ? duree : "—"));
        ((TextView) findViewById(R.id.attente)).setText("⏳ Temps d'attente moyen : " + (attente != null ? attente : "—"));
        ((TextView) findViewById(R.id.description)).setText(
                desc != null && !desc.isEmpty() && !desc.equals("null") ? desc : "Pas de description.");

        TextView seancesView = findViewById(R.id.seances);
        seancesView.setText("Chargement…");

        new ApiClient(this).get("/api/spectacles/" + id + "/seances", res -> {
            if (!res.isSuccess() || res.body == null) {
                seancesView.setText("Impossible de charger les séances.\n(HTTP " + res.status
                        + (res.error != null ? " — " + res.error : "") + ")");
                return;
            }
            try {
                JSONArray arr = new JSONArray(res.body);
                String today = new SimpleDateFormat("yyyy-MM-dd", Locale.FRANCE).format(new Date());
                SimpleDateFormat in  = new SimpleDateFormat("yyyy-MM-dd", Locale.FRANCE);
                SimpleDateFormat out = new SimpleDateFormat("EEEE d MMMM yyyy", Locale.FRANCE);

                StringBuilder sb = new StringBuilder();
                String currentDate = "";
                int kept = 0;
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    String d = o.optString("date_seance");
                    if (d.compareTo(today) < 0) continue; // skip passées
                    kept++;
                    if (!d.equals(currentDate)) {
                        if (sb.length() > 0) sb.append("\n");
                        String pretty;
                        try { pretty = out.format(in.parse(d)); }
                        catch (Exception ex) { pretty = d; }
                        sb.append("📅 ").append(pretty).append("\n");
                        currentDate = d;
                    }
                    sb.append("   • ")
                      .append(o.optString("heure_debut"))
                      .append(" → ")
                      .append(o.optString("heure_fin"))
                      .append("\n");
                }
                if (kept == 0) {
                    seancesView.setText("Aucune séance programmée à venir.");
                } else {
                    seancesView.setText(sb.toString());
                }
            } catch (Exception e) {
                seancesView.setText("Erreur de lecture des séances : " + e.getMessage());
            }
        });
    }
}
