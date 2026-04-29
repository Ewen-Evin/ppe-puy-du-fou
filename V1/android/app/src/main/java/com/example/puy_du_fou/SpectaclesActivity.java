package com.example.puy_du_fou;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.adapter.SpectacleAdapter;
import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.model.Spectacle;
import com.example.puy_du_fou.util.Session;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.HashSet;
import java.util.List;
import java.util.Locale;
import java.util.Set;

public class SpectaclesActivity extends AppCompatActivity {

    private final List<Spectacle> spectacles = new ArrayList<>();
    private SpectacleAdapter adapter;
    private TextView dateText;
    private TextView emptyText;
    private ProgressBar progress;
    private ApiClient api;
    private Session session;

    private int year, month, day;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_spectacles);

        // Sous-écran : flèche retour
        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        tb.setNavigationOnClickListener(v -> finish());

        api     = new ApiClient(this);
        session = new Session(this);

        RecyclerView rv   = findViewById(R.id.recycler);
        progress          = findViewById(R.id.progress);
        emptyText         = findViewById(R.id.emptyText);
        dateText          = findViewById(R.id.dateText);
        Button btnDate     = findViewById(R.id.btnDate);
        Button btnCalculer = findViewById(R.id.btnCalculer);

        adapter = new SpectacleAdapter(spectacles);
        rv.setLayoutManager(new LinearLayoutManager(this));
        rv.setAdapter(adapter);

        Calendar c = Calendar.getInstance();
        year  = c.get(Calendar.YEAR);
        month = c.get(Calendar.MONTH);
        day   = c.get(Calendar.DAY_OF_MONTH);
        updateDateText();

        btnDate.setOnClickListener(v -> new DatePickerDialog(this, (dp, y, m, d) -> {
            year = y; month = m; day = d;
            updateDateText();
            loadSpectacles(); // recharger les séances pour la nouvelle date
        }, year, month, day).show());

        btnCalculer.setOnClickListener(v -> creerVisite());

        loadSpectacles();
    }

    private void updateDateText() {
        dateText.setText(String.format(Locale.FRANCE, "%04d-%02d-%02d", year, month + 1, day));
    }

    private void loadSpectacles() {
        progress.setVisibility(View.VISIBLE);
        emptyText.setVisibility(View.GONE);
        spectacles.clear();
        adapter.notifyDataSetChanged();

        String date = dateText.getText().toString();
        api.get("/api/seances?date=" + date, resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess() || resp.body == null) {
                Toast.makeText(this, "Erreur chargement", Toast.LENGTH_SHORT).show();
                return;
            }
            try {
                JSONArray arr = new JSONArray(resp.body);
                // Dédupliquer : un spectacle peut avoir plusieurs séances le même jour
                // On garde la première occurrence (heure la plus tôt, déjà triée par l'API)
                Set<Integer> seen = new HashSet<>();
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    int idSp = o.optInt("id_spectacle");
                    if (seen.add(idSp)) {
                        spectacles.add(Spectacle.fromJson(o));
                    }
                }
                adapter.notifyDataSetChanged();
                emptyText.setVisibility(spectacles.isEmpty() ? View.VISIBLE : View.GONE);
            } catch (Exception e) {
                Toast.makeText(this, "Format inattendu", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void creerVisite() {
        if (adapter.getSelectedIds().isEmpty()) {
            Toast.makeText(this, "Sélectionne au moins un spectacle", Toast.LENGTH_SHORT).show();
            return;
        }
        try {
            JSONArray ids = new JSONArray();
            for (Integer id : adapter.getSelectedIds()) ids.put(id);

            String dateVisite = dateText.getText().toString();
            JSONObject body = new JSONObject()
                    .put("date_visite", dateVisite)
                    .put("vitesse_marche", session.getVitesse())
                    .put("spectacles", ids);

            progress.setVisibility(View.VISIBLE);
            api.post("/api/parcours/preview", body.toString(), resp -> {
                progress.setVisibility(View.GONE);
                if (!resp.isSuccess() || resp.body == null) {
                    Toast.makeText(this, "Erreur calcul parcours", Toast.LENGTH_SHORT).show();
                    return;
                }
                try {
                    JSONArray parcours = new JSONObject(resp.body).getJSONArray("parcours");
                    Intent it = new Intent(this, ParcoursActivity.class);
                    it.putExtra("mode", "preview");
                    it.putExtra("parcours_json", parcours.toString());
                    it.putExtra("date_visite", dateVisite);
                    it.putExtra("vitesse_marche", session.getVitesse());
                    it.putExtra("spectacles_json", ids.toString());
                    startActivity(it);
                } catch (Exception e) {
                    Toast.makeText(this, "Réponse inattendue", Toast.LENGTH_SHORT).show();
                }
            });
        } catch (Exception e) {
            Toast.makeText(this, "Erreur", Toast.LENGTH_SHORT).show();
        }
    }
}
