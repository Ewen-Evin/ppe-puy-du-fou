package com.example.puy_du_fou.adapter;

import android.graphics.Color;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.model.Parcours;

import java.util.List;

public class ParcoursAdapter extends RecyclerView.Adapter<ParcoursAdapter.VH> {

    public interface OnSelectListener { void onSelect(int index); }

    private final List<Parcours> items;
    private final boolean selectable;
    private final OnSelectListener listener;
    private int selectedIndex = 0;

    /** Constructeur lecture seule (historique sans édition). */
    public ParcoursAdapter(List<Parcours> items) {
        this(items, false, null);
    }

    /** Constructeur avec sélection (preview ou historique éditable). */
    public ParcoursAdapter(List<Parcours> items, boolean selectable, OnSelectListener listener) {
        this.items = items;
        this.selectable = selectable;
        this.listener = listener;
    }

    public void setSelectedIndex(int index) {
        int prev = selectedIndex;
        selectedIndex = index;
        notifyItemChanged(prev);
        notifyItemChanged(selectedIndex);
    }

    @NonNull @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_parcours, parent, false);
        return new VH(v);
    }

    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        Parcours p = items.get(position);
        boolean isSelected = selectable
                ? (position == selectedIndex)
                : p.favori;

        // ── Titre ──────────────────────────────────────────────────────────
        String statut = p.complet ? "Complet ✓" : "Partiel ⚠";
        String prefix = isSelected && !selectable ? "⭐ " : "";
        h.titre.setText(prefix + "Option " + (position + 1) + "  —  " + statut);

        // ── Résumé ─────────────────────────────────────────────────────────
        String duree   = formatDuree(p.dureeMin);
        String attente = p.attenteMin > 0 ? "  •  " + p.attenteMin + " min d'attente" : "";
        h.resume.setText(duree + attente);

        // ── Étapes ─────────────────────────────────────────────────────────
        StringBuilder sb = new StringBuilder();
        for (Parcours.Etape e : p.etapes) {
            String nom = (e.libelle != null && !e.libelle.isEmpty())
                    ? e.libelle : "Spectacle #" + e.idSpectacle;
            sb.append(e.ordre).append(". ")
              .append(nom).append("\n")
              .append("   ").append(fmt(e.heureDebut))
              .append(" → ").append(fmt(e.heureFin)).append("\n\n");
        }
        h.etapes.setText(sb.toString().trim());

        // ── Bouton de sélection (preview uniquement) ───────────────────────
        if (selectable) {
            h.btnSelect.setVisibility(View.VISIBLE);
            if (isSelected) {
                h.btnSelect.setText("✓ Itinéraire principal");
                h.btnSelect.setTextColor(Color.parseColor("#2E7D32"));
                h.root.setBackgroundColor(Color.parseColor("#F1F8F1"));
            } else {
                h.btnSelect.setText("Choisir cet itinéraire");
                h.btnSelect.setTextColor(Color.parseColor("#8C1C13"));
                h.root.setBackgroundColor(Color.WHITE);
            }
            h.btnSelect.setOnClickListener(v -> {
                int pos = h.getAdapterPosition();
                if (pos != RecyclerView.NO_ID) {
                    setSelectedIndex(pos);
                    if (listener != null) listener.onSelect(pos);
                }
            });
            h.root.setOnClickListener(v -> h.btnSelect.performClick());
        } else {
            h.btnSelect.setVisibility(View.GONE);
            // En mode historique, mettre en avant le favori avec un fond doux
            h.root.setBackgroundColor(isSelected
                    ? Color.parseColor("#F1F8F1") : Color.WHITE);
        }
    }

    @Override public int getItemCount() { return items.size(); }

    private static String formatDuree(int min) {
        if (min < 60) return min + " min";
        int h = min / 60, m = min % 60;
        return m == 0 ? h + "h" : h + "h" + String.format("%02d", m);
    }

    private static String fmt(String time) {
        if (time == null || time.length() < 5) return time != null ? time : "";
        return time.substring(0, 5).replace(":", "h");
    }

    static class VH extends RecyclerView.ViewHolder {
        LinearLayout root;
        TextView titre, resume, etapes, btnSelect;
        VH(View v) {
            super(v);
            root      = v.findViewById(R.id.cardRoot);
            titre     = v.findViewById(R.id.titre);
            resume    = v.findViewById(R.id.resume);
            etapes    = v.findViewById(R.id.etapes);
            btnSelect = v.findViewById(R.id.btnSelect);
        }
    }
}
