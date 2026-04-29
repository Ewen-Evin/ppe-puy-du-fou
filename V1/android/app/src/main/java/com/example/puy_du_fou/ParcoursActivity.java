package com.example.puy_du_fou;

import android.content.Intent;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.CarteActivity;
import com.example.puy_du_fou.adapter.ParcoursAdapter;
import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.model.Parcours;
import com.google.android.material.button.MaterialButton;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class ParcoursActivity extends AppCompatActivity {

    private final List<Parcours> parcoursList = new ArrayList<>();
    private ParcoursAdapter adapter;
    private ProgressBar progress;
    private TextView emptyText;
    private MaterialButton btnAction; // "Enregistrer" en preview, "Supprimer" en historique
    private MaterialButton btnCarte;

    // Mode preview
    private String dateVisite;
    private double vitesseMarche;
    private String spectaclesJson;
    private int    selectedParcoursIndex = 0;

    // Mode historique
    private int    idVisite = 0;
    private ApiClient api;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_parcours);

        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        tb.setNavigationOnClickListener(v -> finish());

        RecyclerView rv = findViewById(R.id.recycler);
        progress  = findViewById(R.id.progress);
        emptyText = findViewById(R.id.emptyText);
        btnAction = findViewById(R.id.btnSave);
        btnCarte  = findViewById(R.id.btnCarte);
        api       = new ApiClient(this);

        rv.setLayoutManager(new LinearLayoutManager(this));

        String mode = getIntent().getStringExtra("mode");

        if ("preview".equals(mode)) {
            adapter = new ParcoursAdapter(parcoursList, true,
                    index -> selectedParcoursIndex = index);
            rv.setAdapter(adapter);
            setupPreviewButton();
            loadFromIntent();
        } else {
            idVisite = getIntent().getIntExtra("id_visite", 0);
            if (idVisite <= 0) {
                Toast.makeText(this, "Visite invalide", Toast.LENGTH_SHORT).show();
                finish();
                return;
            }
            adapter = new ParcoursAdapter(parcoursList, true,
                    index -> updateFavori(index));
            rv.setAdapter(adapter);
            setupDeleteButton();
            loadFromApi(idVisite);
        }
    }

    // ─── Mode preview ───────────────────────────────────────────────────────

    private void setupPreviewButton() {
        btnAction.setVisibility(View.VISIBLE);
        btnAction.setText("Enregistrer cette visite");
        btnAction.setOnClickListener(v -> showSaveDialog());
    }

    private void loadFromIntent() {
        dateVisite     = getIntent().getStringExtra("date_visite");
        vitesseMarche  = getIntent().getDoubleExtra("vitesse_marche", 4.0);
        spectaclesJson = getIntent().getStringExtra("spectacles_json");

        String parcoursJson = getIntent().getStringExtra("parcours_json");
        if (parcoursJson == null) { showEmpty("Aucun parcours calculé."); return; }
        try {
            JSONArray arr = new JSONArray(parcoursJson);
            for (int i = 0; i < arr.length(); i++) {
                parcoursList.add(Parcours.fromJson(arr.getJSONObject(i)));
            }
        } catch (Exception e) { showEmpty("Erreur de lecture du parcours."); return; }

        if (parcoursList.isEmpty()) {
            showEmpty("Aucun parcours possible pour cette sélection.");
        } else {
            adapter.notifyDataSetChanged();
        }
    }

    private void showSaveDialog() {
        FrameLayout container = new FrameLayout(this);
        EditText input = new EditText(this);
        input.setHint("Ex : Visite en famille");
        input.setSingleLine(true);
        int px = (int)(16 * getResources().getDisplayMetrics().density);
        FrameLayout.LayoutParams lp = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        lp.setMargins(px, px / 2, px, 0);
        input.setLayoutParams(lp);
        container.addView(input);

        new AlertDialog.Builder(this)
                .setTitle("Nommer votre visite")
                .setView(container)
                .setPositiveButton("Enregistrer", (dialog, which) -> {
                    String nom = input.getText().toString().trim();
                    if (nom.isEmpty()) nom = "Visite du " + dateVisite;
                    saveVisite(nom);
                })
                .setNegativeButton("Annuler", null)
                .show();
    }

    private void saveVisite(String nom) {
        try {
            JSONObject body = new JSONObject()
                    .put("date_visite", dateVisite)
                    .put("vitesse_marche", vitesseMarche)
                    .put("spectacles", new JSONArray(spectaclesJson))
                    .put("nom_visite", nom)
                    .put("parcours_favori", selectedParcoursIndex);

            btnAction.setEnabled(false);
            btnAction.setText("Enregistrement…");

            api.post("/api/visites", body.toString(), resp -> {
                if (resp.isSuccess()) {
                    Toast.makeText(this, "Visite \"" + nom + "\" enregistrée !", Toast.LENGTH_LONG).show();
                    Intent it = new Intent(this, HistoriqueActivity.class);
                    it.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                    startActivity(it);
                    finish();
                } else {
                    Toast.makeText(this, "Erreur lors de l'enregistrement", Toast.LENGTH_SHORT).show();
                    btnAction.setEnabled(true);
                    btnAction.setText("Enregistrer cette visite");
                }
            });
        } catch (Exception e) {
            Toast.makeText(this, "Erreur", Toast.LENGTH_SHORT).show();
            btnAction.setEnabled(true);
        }
    }

    // ─── Mode historique ─────────────────────────────────────────────────────

    private void setupDeleteButton() {
        btnAction.setVisibility(View.VISIBLE);
        btnAction.setText("Supprimer cette visite");
        btnAction.setBackgroundTintList(
                ColorStateList.valueOf(Color.parseColor("#C62828")));
        btnAction.setOnClickListener(v -> confirmDelete());

        btnCarte.setVisibility(View.VISIBLE);
        btnCarte.setOnClickListener(v -> {
            Intent it = new Intent(this, CarteActivity.class);
            it.putExtra("id_visite", idVisite);
            startActivity(it);
        });
    }

    private void loadFromApi(int id) {
        progress.setVisibility(View.VISIBLE);
        api.get("/api/visites/" + id + "/parcours", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess() || resp.body == null) {
                showEmpty("Erreur de chargement");
                return;
            }
            try {
                JSONObject json = new JSONObject(resp.body);

                // Titre = nom de la visite ou date
                JSONObject visite = json.optJSONObject("visite");
                if (visite != null) {
                    String nom  = visite.optString("nom_visite", "");
                    String date = visite.optString("date_visite", "");
                    ((androidx.appcompat.widget.Toolbar) findViewById(R.id.toolbar))
                            .setTitle(nom.isEmpty() ? "Visite du " + date : nom);
                }

                JSONArray arr = json.optJSONArray("parcours");
                parcoursList.clear();
                int initSelected = 0;
                if (arr != null) {
                    for (int i = 0; i < arr.length(); i++) {
                        Parcours p = Parcours.fromJson(arr.getJSONObject(i));
                        if (p.favori) initSelected = i;
                        parcoursList.add(p);
                    }
                }
                // Positionner la sélection sur le favori actuel
                adapter.setSelectedIndex(initSelected);
                if (parcoursList.isEmpty()) showEmpty("Aucun parcours pour cette visite.");
                adapter.notifyDataSetChanged();
            } catch (Exception e) {
                showEmpty("Format inattendu");
            }
        });
    }

    /** Appelé par l'adapter quand l'utilisateur tape sur "Choisir cet itinéraire" */
    private void updateFavori(int index) {
        try {
            JSONObject body = new JSONObject().put("parcours_favori", index);
            api.put("/api/visites/" + idVisite + "/favori", body.toString(), resp -> {
                if (resp.isSuccess()) {
                    Toast.makeText(this, "Itinéraire favori mis à jour", Toast.LENGTH_SHORT).show();
                } else {
                    Toast.makeText(this, "Erreur mise à jour", Toast.LENGTH_SHORT).show();
                }
            });
        } catch (Exception ignored) {}
    }

    private void confirmDelete() {
        new AlertDialog.Builder(this)
                .setTitle("Supprimer cette visite ?")
                .setMessage("Cette action est irréversible.")
                .setPositiveButton("Supprimer", (dialog, which) -> deleteVisite())
                .setNegativeButton("Annuler", null)
                .show();
    }

    private void deleteVisite() {
        btnAction.setEnabled(false);
        btnAction.setText("Suppression…");
        api.delete("/api/visites/" + idVisite, resp -> {
            if (resp.isSuccess()) {
                Toast.makeText(this, "Visite supprimée", Toast.LENGTH_SHORT).show();
                Intent it = new Intent(this, HistoriqueActivity.class);
                it.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
                startActivity(it);
                finish();
            } else {
                Toast.makeText(this, "Erreur lors de la suppression", Toast.LENGTH_SHORT).show();
                btnAction.setEnabled(true);
                btnAction.setText("Supprimer cette visite");
            }
        });
    }

    // ─── Helper ─────────────────────────────────────────────────────────────

    private void showEmpty(String msg) {
        emptyText.setText(msg);
        emptyText.setVisibility(View.VISIBLE);
    }
}
