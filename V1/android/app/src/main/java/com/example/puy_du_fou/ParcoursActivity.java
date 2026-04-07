package com.example.puy_du_fou;

import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.adapter.ParcoursAdapter;
import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.model.Parcours;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class ParcoursActivity extends AppCompatActivity {

    private final List<Parcours> parcoursList = new ArrayList<>();
    private ParcoursAdapter adapter;
    private ProgressBar progress;
    private TextView emptyText;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_parcours);

        int idVisite = getIntent().getIntExtra("id_visite", 0);

        RecyclerView rv = findViewById(R.id.recycler);
        progress  = findViewById(R.id.progress);
        emptyText = findViewById(R.id.emptyText);

        adapter = new ParcoursAdapter(parcoursList);
        rv.setLayoutManager(new LinearLayoutManager(this));
        rv.setAdapter(adapter);

        if (idVisite <= 0) {
            Toast.makeText(this, "Visite invalide", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/visites/" + idVisite + "/parcours", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess() || resp.body == null) {
                emptyText.setText("Erreur de chargement");
                emptyText.setVisibility(View.VISIBLE);
                return;
            }
            try {
                JSONObject json = new JSONObject(resp.body);
                JSONArray arr = json.optJSONArray("parcours");
                parcoursList.clear();
                if (arr != null) {
                    for (int i = 0; i < arr.length(); i++) {
                        parcoursList.add(Parcours.fromJson(arr.getJSONObject(i)));
                    }
                }
                if (parcoursList.isEmpty()) {
                    emptyText.setText("Aucun parcours possible pour cette sélection.");
                    emptyText.setVisibility(View.VISIBLE);
                }
                adapter.notifyDataSetChanged();
            } catch (Exception e) {
                emptyText.setText("Format inattendu");
                emptyText.setVisibility(View.VISIBLE);
            }
        });
    }
}
