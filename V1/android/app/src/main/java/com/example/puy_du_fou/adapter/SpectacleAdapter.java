package com.example.puy_du_fou.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.model.Spectacle;

import java.util.HashSet;
import java.util.List;
import java.util.Set;

public class SpectacleAdapter extends RecyclerView.Adapter<SpectacleAdapter.VH> {

    public interface OnSpectacleClick { void onClick(Spectacle s); }

    private final List<Spectacle> items;
    private final Set<Integer> selected = new HashSet<>();
    private final boolean browseMode;
    private final OnSpectacleClick clickListener;

    public SpectacleAdapter(List<Spectacle> items) {
        this(items, false, null);
    }

    public SpectacleAdapter(List<Spectacle> items, boolean browseMode, OnSpectacleClick listener) {
        this.items = items;
        this.browseMode = browseMode;
        this.clickListener = listener;
    }

    public Set<Integer> getSelectedIds() { return selected; }

    @NonNull @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_spectacle, parent, false);
        return new VH(v);
    }

    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        Spectacle s = items.get(position);
        h.titre.setText(s.libelle);
        // Si construit depuis une séance : afficher l'heure, sinon lieu + durée
        if (s.heureDebut != null && !s.heureDebut.isEmpty()) {
            h.lieu.setText("Séance à " + formatHeure(s.heureDebut));
        } else {
            h.lieu.setText(s.lieuNom + "  •  " + s.dureeSpectacle);
        }
        if (browseMode) {
            h.checkBox.setVisibility(View.GONE);
            h.itemView.setOnClickListener(v -> {
                if (clickListener != null) clickListener.onClick(s);
            });
        } else {
            h.checkBox.setVisibility(View.VISIBLE);
            h.checkBox.setOnCheckedChangeListener(null);
            h.checkBox.setChecked(selected.contains(s.id));
            h.checkBox.setOnCheckedChangeListener((cb, isChecked) -> {
                if (isChecked) selected.add(s.id);
                else selected.remove(s.id);
            });
            h.itemView.setOnClickListener(v -> h.checkBox.toggle());
        }
    }

    @Override public int getItemCount() { return items.size(); }

    private static String formatHeure(String time) {
        if (time == null || time.length() < 5) return time != null ? time : "";
        return time.substring(0, 5).replace(":", "h");
    }

    static class VH extends RecyclerView.ViewHolder {
        CheckBox checkBox;
        TextView titre, lieu;
        VH(View v) {
            super(v);
            checkBox = v.findViewById(R.id.checkBox);
            titre    = v.findViewById(R.id.titre);
            lieu     = v.findViewById(R.id.lieu);
        }
    }
}
