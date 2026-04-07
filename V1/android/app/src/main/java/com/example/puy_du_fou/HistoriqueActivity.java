package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ListView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.api.ApiClient;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class HistoriqueActivity extends AppCompatActivity {

    static class VisiteRow {
        int id;
        String date;
        @NonNull @Override public String toString() { return "Visite #" + id + "  —  " + date; }
    }

    private final List<VisiteRow> visites = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_historique);

        ListView list = findViewById(R.id.list);
        ProgressBar progress = findViewById(R.id.progress);
        TextView empty = findViewById(R.id.empty);

        ArrayAdapter<VisiteRow> adapter = new ArrayAdapter<>(this,
                android.R.layout.simple_list_item_1, visites);
        list.setAdapter(adapter);

        list.setOnItemClickListener((parent, view, position, id) -> {
            Intent it = new Intent(this, ParcoursActivity.class);
            it.putExtra("id_visite", visites.get(position).id);
            startActivity(it);
        });

        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/visites", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess() || resp.body == null) {
                Toast.makeText(this, "Erreur chargement", Toast.LENGTH_SHORT).show();
                return;
            }
            try {
                JSONArray arr = new JSONArray(resp.body);
                visites.clear();
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    VisiteRow v = new VisiteRow();
                    v.id   = o.optInt("id_visite");
                    v.date = o.optString("date_visite");
                    if (v.id > 0) visites.add(v);
                }
                if (visites.isEmpty()) {
                    empty.setVisibility(View.VISIBLE);
                }
                adapter.notifyDataSetChanged();
            } catch (Exception e) {
                Toast.makeText(this, "Format inattendu", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
