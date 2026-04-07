package com.example.puy_du_fou.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.model.Parcours;

import java.util.List;

public class ParcoursAdapter extends RecyclerView.Adapter<ParcoursAdapter.VH> {

    private final List<Parcours> items;

    public ParcoursAdapter(List<Parcours> items) { this.items = items; }

    @NonNull @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_parcours, parent, false);
        return new VH(v);
    }

    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        Parcours p = items.get(position);
        h.titre.setText("Parcours #" + (position + 1) + (p.complet ? " — Complet ✅" : " — Partiel ⚠️"));
        h.resume.setText("Durée : " + p.dureeMin + " min   •   Attente : " + p.attenteMin + " min");

        StringBuilder sb = new StringBuilder();
        for (Parcours.Etape e : p.etapes) {
            sb.append(e.ordre).append(". ")
              .append(e.heureDebut).append(" → ").append(e.heureFin)
              .append("  (spectacle #").append(e.idSpectacle).append(")\n");
        }
        h.etapes.setText(sb.toString().trim());
    }

    @Override public int getItemCount() { return items.size(); }

    static class VH extends RecyclerView.ViewHolder {
        TextView titre, resume, etapes;
        VH(View v) {
            super(v);
            titre  = v.findViewById(R.id.titre);
            resume = v.findViewById(R.id.resume);
            etapes = v.findViewById(R.id.etapes);
        }
    }
}
