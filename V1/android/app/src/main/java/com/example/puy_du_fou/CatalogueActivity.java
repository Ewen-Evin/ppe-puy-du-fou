package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.DividerItemDecoration;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.adapter.CatalogueAdapter;
import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.model.CatalogueItem;
import com.example.puy_du_fou.util.NavHelper;
import com.example.puy_du_fou.util.RecentlyViewed;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.chip.Chip;
import com.google.android.material.chip.ChipGroup;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class CatalogueActivity extends AppCompatActivity {

    private final List<CatalogueItem> allItems      = new ArrayList<>();
    private final List<CatalogueItem> displayedItems = new ArrayList<>();
    private CatalogueAdapter adapter;
    private ProgressBar progress;
    private TextView emptyText;
    private String currentFilter = "tous";

    // Pour synchroniser les 2 appels API
    private int     pendingLoads   = 2;
    private JSONArray lieuxJson     = null;
    private JSONArray spectaclesJson = null;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_catalogue);

        NavHelper.setup(this, (BottomNavigationView) findViewById(R.id.bottomNav), R.id.nav_catalogue);

        progress  = findViewById(R.id.progress);
        emptyText = findViewById(R.id.emptyText);

        // RecyclerView
        RecyclerView rv = findViewById(R.id.recycler);
        rv.setLayoutManager(new LinearLayoutManager(this));
        rv.addItemDecoration(new DividerItemDecoration(this, DividerItemDecoration.VERTICAL));
        adapter = new CatalogueAdapter(displayedItems, item -> {
            RecentlyViewed.save(this, item.idLieu, item.libelleSpectacle,
                    item.descriptionSpectacle, item.dureeSpectacle, item.dureeAttente, item.nomLieu);
            Intent i = new Intent(this, SpectacleDetailActivity.class);
            i.putExtra("id_spectacle", item.idSpectacle);
            i.putExtra("libelle",      item.libelleSpectacle);
            i.putExtra("description",  item.descriptionSpectacle);
            i.putExtra("duree",        item.dureeSpectacle);
            i.putExtra("attente",      item.dureeAttente);
            i.putExtra("lieu",         item.nomLieu);
            startActivity(i);
        });
        rv.setAdapter(adapter);

        // Chips
        ChipGroup chips = findViewById(R.id.chipGroup);
        chips.setOnCheckedStateChangeListener((group, checkedIds) -> {
            if (checkedIds.isEmpty()) return;
            Chip chip = group.findViewById(checkedIds.get(0));
            if (chip != null) {
                currentFilter = chip.getTag().toString();
                applyFilter();
            }
        });

        // FAB → SpectaclesActivity
        findViewById(R.id.fabPlanifier).setOnClickListener(v -> {
            startActivity(new Intent(this, SpectaclesActivity.class));
        });

        progress.setVisibility(View.VISIBLE);
        loadData();
    }

    @Override
    protected void onResume() {
        super.onResume();
        NavHelper.syncSelection(this, R.id.nav_catalogue);
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
    }

    // ─── Chargement ─────────────────────────────────────────────────────────

    private void loadData() {
        ApiClient api = new ApiClient(this);

        api.get("/api/lieux", resp -> {
            if (resp.isSuccess() && resp.body != null) {
                try { lieuxJson = new JSONArray(resp.body); } catch (Exception ignored) {}
            }
            checkReady();
        });

        api.get("/api/spectacles", resp -> {
            if (resp.isSuccess() && resp.body != null) {
                try { spectaclesJson = new JSONArray(resp.body); } catch (Exception ignored) {}
            }
            checkReady();
        });
    }

    private synchronized void checkReady() {
        pendingLoads--;
        if (pendingLoads == 0) {
            runOnUiThread(this::mergeAndDisplay);
        }
    }

    private void mergeAndDisplay() {
        progress.setVisibility(View.GONE);

        if (lieuxJson == null) {
            Toast.makeText(this, "Erreur de chargement", Toast.LENGTH_SHORT).show();
            return;
        }

        // Map idLieu → spectacle
        Map<Integer, JSONObject> spectacleByLieu = new HashMap<>();
        if (spectaclesJson != null) {
            for (int i = 0; i < spectaclesJson.length(); i++) {
                try {
                    JSONObject sp = spectaclesJson.getJSONObject(i);
                    spectacleByLieu.put(sp.getInt("id_lieu"), sp);
                } catch (Exception ignored) {}
            }
        }

        allItems.clear();
        for (int i = 0; i < lieuxJson.length(); i++) {
            try {
                JSONObject lieu = lieuxJson.getJSONObject(i);
                String type = lieu.optString("type_lieu", "zone");
                if ("allee".equals(type)) continue; // on masque les allées

                CatalogueItem item = new CatalogueItem();
                item.idLieu   = lieu.getInt("id_lieu");
                item.nomLieu  = lieu.getString("nom");
                item.typeLieu = type;

                JSONObject sp = spectacleByLieu.get(item.idLieu);
                if (sp != null) {
                    item.idSpectacle          = sp.getInt("id_spectacle");
                    item.libelleSpectacle     = sp.optString("libelle");
                    item.descriptionSpectacle = sp.optString("description");
                    item.dureeSpectacle       = sp.optString("duree_spectacle");
                    item.dureeAttente         = sp.optString("duree_attente");
                }

                allItems.add(item);
            } catch (Exception ignored) {}
        }

        // Tri : spectacles en premier, puis par type, puis par nom
        Collections.sort(allItems, (a, b) -> {
            int oa = typeOrder(a.typeLieu), ob = typeOrder(b.typeLieu);
            if (oa != ob) return oa - ob;
            return a.nomLieu.compareToIgnoreCase(b.nomLieu);
        });

        applyFilter();
    }

    private void applyFilter() {
        displayedItems.clear();
        for (CatalogueItem item : allItems) {
            if ("tous".equals(currentFilter) || currentFilter.equals(item.typeLieu)) {
                displayedItems.add(item);
            }
        }
        adapter.notifyDataSetChanged();
        emptyText.setVisibility(displayedItems.isEmpty() ? View.VISIBLE : View.GONE);
    }

    private static int typeOrder(String type) {
        switch (type) {
            case "spectacle":  return 0;
            case "zone":       return 1;
            case "restaurant": return 2;
            case "hotel":      return 3;
            case "accueil":    return 4;
            default:           return 5;
        }
    }
}
