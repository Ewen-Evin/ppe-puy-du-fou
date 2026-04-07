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
import java.util.List;
import java.util.Locale;

public class SpectaclesActivity extends AppCompatActivity {

    private final List<Spectacle> spectacles = new ArrayList<>();
    private SpectacleAdapter adapter;
    private TextView dateText;
    private ProgressBar progress;
    private ApiClient api;
    private Session session;

    private int year, month, day;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_spectacles);

        api     = new ApiClient(this);
        session = new Session(this);

        RecyclerView rv = findViewById(R.id.recycler);
        progress  = findViewById(R.id.progress);
        dateText  = findViewById(R.id.dateText);
        Button btnDate    = findViewById(R.id.btnDate);
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
        }, year, month, day).show());

        btnCalculer.setOnClickListener(v -> creerVisite());

        loadSpectacles();
    }

    private void updateDateText() {
        dateText.setText(String.format(Locale.FRANCE, "%04d-%02d-%02d", year, month + 1, day));
    }

    private void loadSpectacles() {
        progress.setVisibility(View.VISIBLE);
        api.get("/api/spectacles", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess() || resp.body == null) {
                Toast.makeText(this, "Erreur chargement", Toast.LENGTH_SHORT).show();
                return;
            }
            try {
                JSONArray arr = new JSONArray(resp.body);
                spectacles.clear();
                for (int i = 0; i < arr.length(); i++) {
                    spectacles.add(Spectacle.fromJson(arr.getJSONObject(i)));
                }
                adapter.notifyDataSetChanged();
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

            JSONObject body = new JSONObject()
                    .put("date_visite", dateText.getText().toString())
                    .put("vitesse_marche", session.getVitesse())
                    .put("spectacles", ids);

            progress.setVisibility(View.VISIBLE);
            api.post("/api/visites", body.toString(), resp -> {
                progress.setVisibility(View.GONE);
                if (!resp.isSuccess() || resp.body == null) {
                    Toast.makeText(this, "Erreur création visite", Toast.LENGTH_SHORT).show();
                    return;
                }
                try {
                    int idVisite = new JSONObject(resp.body).getInt("id_visite");
                    Intent it = new Intent(this, ParcoursActivity.class);
                    it.putExtra("id_visite", idVisite);
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
