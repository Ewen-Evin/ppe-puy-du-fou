package com.example.puy_du_fou;

import android.content.Intent;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.DividerItemDecoration;
import androidx.recyclerview.widget.ItemTouchHelper;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.NavHelper;
import com.google.android.material.bottomnavigation.BottomNavigationView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class HistoriqueActivity extends AppCompatActivity {

    // ─── Modèle ─────────────────────────────────────────────────────────────

    static class VisiteRow {
        int    id;
        String date;
        String nom;

        String displayName() {
            return (nom != null && !nom.isEmpty()) ? nom : "Visite #" + id;
        }
    }

    // ─── Adapter interne ────────────────────────────────────────────────────

    class VisiteAdapter extends RecyclerView.Adapter<VisiteAdapter.VH> {

        class VH extends RecyclerView.ViewHolder {
            TextView name, date;

            VH(View v) {
                super(v);
                name = v.findViewById(R.id.visiteName);
                date = v.findViewById(R.id.visiteDate);
                v.setOnClickListener(view -> {
                    int pos = getAdapterPosition();
                    if (pos == RecyclerView.NO_ID) return;
                    Intent it = new Intent(HistoriqueActivity.this, ParcoursActivity.class);
                    it.putExtra("id_visite", visites.get(pos).id);
                    startActivity(it);
                });
            }
        }

        @NonNull @Override
        public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View v = LayoutInflater.from(parent.getContext())
                    .inflate(R.layout.item_visite, parent, false);
            return new VH(v);
        }

        @Override
        public void onBindViewHolder(@NonNull VH h, int position) {
            VisiteRow row = visites.get(position);
            h.name.setText(row.displayName());
            h.date.setText(row.date);
        }

        @Override public int getItemCount() { return visites.size(); }
    }

    // ─── Activité ───────────────────────────────────────────────────────────

    private final List<VisiteRow> visites = new ArrayList<>();
    private VisiteAdapter rvAdapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_historique);

        NavHelper.setup(this, (BottomNavigationView) findViewById(R.id.bottomNav),
                R.id.nav_historique);

        RecyclerView rv     = findViewById(R.id.list);
        ProgressBar progress = findViewById(R.id.progress);
        TextView empty      = findViewById(R.id.empty);

        rvAdapter = new VisiteAdapter();
        rv.setLayoutManager(new LinearLayoutManager(this));
        rv.addItemDecoration(new DividerItemDecoration(this, DividerItemDecoration.VERTICAL));
        rv.setAdapter(rvAdapter);

        // ── Swipe gauche = suppression ──────────────────────────────────────
        new ItemTouchHelper(new ItemTouchHelper.SimpleCallback(0, ItemTouchHelper.LEFT) {

            @Override
            public boolean onMove(@NonNull RecyclerView rv,
                                  @NonNull RecyclerView.ViewHolder a,
                                  @NonNull RecyclerView.ViewHolder b) {
                return false;
            }

            @Override
            public float getSwipeThreshold(@NonNull RecyclerView.ViewHolder viewHolder) {
                return 0.40f; // 40 % de largeur pour déclencher
            }

            @Override
            public void onSwiped(@NonNull RecyclerView.ViewHolder viewHolder, int direction) {
                int pos = viewHolder.getAdapterPosition();
                if (pos == RecyclerView.NO_ID) return;
                VisiteRow row = visites.get(pos);

                new AlertDialog.Builder(HistoriqueActivity.this)
                        .setTitle("Supprimer \"" + row.displayName() + "\" ?")
                        .setMessage("Cette action est irréversible.")
                        .setPositiveButton("Supprimer", (d, w) ->
                                deleteVisite(pos, row.id, empty))
                        .setNegativeButton("Annuler", (d, w) ->
                                rvAdapter.notifyItemChanged(pos))
                        .setOnCancelListener(d ->
                                rvAdapter.notifyItemChanged(pos))
                        .show();
            }

            /** Dessine le fond rouge + icône poubelle derrière l'élément glissé. */
            @Override
            public void onChildDraw(@NonNull Canvas c,
                                    @NonNull RecyclerView recyclerView,
                                    @NonNull RecyclerView.ViewHolder viewHolder,
                                    float dX, float dY, int actionState, boolean isCurrentlyActive) {

                if (actionState == ItemTouchHelper.ACTION_STATE_SWIPE && dX < 0) {
                    View item = viewHolder.itemView;
                    float swipeRatio = Math.abs(dX) / item.getWidth();

                    // Fond rouge (s'élargit au fur et à mesure du swipe)
                    Paint paint = new Paint(Paint.ANTI_ALIAS_FLAG);
                    paint.setColor(Color.parseColor("#C62828"));
                    c.drawRect(item.getRight() + dX, item.getTop(),
                               item.getRight(), item.getBottom(), paint);

                    // Icône poubelle (apparaît progressivement dès 15 % de swipe)
                    Drawable icon = ContextCompat.getDrawable(
                            HistoriqueActivity.this, R.drawable.ic_trash);
                    if (icon != null) {
                        int iconSize   = (int) (item.getHeight() * 0.40f);
                        int iconMargin = (item.getHeight() - iconSize) / 2;
                        int iconLeft   = item.getRight() - iconMargin - iconSize;
                        int iconTop    = item.getTop()  + iconMargin;

                        // Afficher l'icône seulement si assez d'espace révélé
                        if (item.getRight() + dX < iconLeft - 8) {
                            float alpha = Math.min(1f, (swipeRatio - 0.10f) / 0.15f);
                            icon.setAlpha((int)(alpha * 255));
                            icon.setBounds(iconLeft, iconTop,
                                           iconLeft + iconSize, iconTop + iconSize);
                            icon.draw(c);
                        }
                    }
                }
                super.onChildDraw(c, recyclerView, viewHolder,
                        dX, dY, actionState, isCurrentlyActive);
            }

        }).attachToRecyclerView(rv);

        // ── Chargement des visites ──────────────────────────────────────────
        progress.setVisibility(View.VISIBLE);
        new ApiClient(this).get("/api/visites", resp -> {
            progress.setVisibility(View.GONE);
            if (!resp.isSuccess()) {
                String msg = resp.status == 0
                        ? "Serveur inaccessible : " + resp.error
                        : "Erreur " + resp.status + " : " + (resp.body != null ? resp.body : "");
                Toast.makeText(this, msg, Toast.LENGTH_LONG).show();
                return;
            }
            try {
                if (resp.body == null || resp.body.isEmpty()) {
                    Toast.makeText(this, "Réponse vide du serveur", Toast.LENGTH_LONG).show();
                    return;
                }
                String trimmed = resp.body.trim();
                if (!trimmed.startsWith("[")) {
                    Toast.makeText(this, "Réponse inattendue : " + trimmed, Toast.LENGTH_LONG).show();
                    return;
                }
                JSONArray arr = new JSONArray(trimmed);
                visites.clear();
                for (int i = 0; i < arr.length(); i++) {
                    JSONObject o = arr.getJSONObject(i);
                    VisiteRow v = new VisiteRow();
                    v.id   = o.optInt("id_visite");
                    v.date = o.optString("date_visite");
                    v.nom  = o.optString("nom_visite");
                    if (v.id > 0) visites.add(v);
                }
                empty.setVisibility(visites.isEmpty() ? View.VISIBLE : View.GONE);
                rvAdapter.notifyDataSetChanged();
            } catch (Exception e) {
                Toast.makeText(this, "Erreur: " + e.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    private void deleteVisite(int pos, int idVisite, TextView empty) {
        new ApiClient(this).delete("/api/visites/" + idVisite, resp -> {
            if (resp.isSuccess()) {
                visites.remove(pos);
                rvAdapter.notifyItemRemoved(pos);
                empty.setVisibility(visites.isEmpty() ? View.VISIBLE : View.GONE);
                Toast.makeText(this, "Visite supprimée", Toast.LENGTH_SHORT).show();
            } else {
                rvAdapter.notifyItemChanged(pos); // remet l'élément en place
                Toast.makeText(this, "Erreur lors de la suppression", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        NavHelper.syncSelection(this, R.id.nav_historique);
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
    }
}
