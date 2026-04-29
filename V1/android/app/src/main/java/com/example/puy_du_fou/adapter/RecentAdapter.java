package com.example.puy_du_fou.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.util.RecentlyViewed;

import java.util.List;

public class RecentAdapter extends RecyclerView.Adapter<RecentAdapter.VH> {

    public interface OnClick {
        void onClick(RecentlyViewed.Item item);
    }

    private final List<RecentlyViewed.Item> items;
    private final OnClick listener;

    public RecentAdapter(List<RecentlyViewed.Item> items, OnClick listener) {
        this.items    = items;
        this.listener = listener;
    }

    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_recent_spectacle, parent, false);
        return new VH(v);
    }

    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        RecentlyViewed.Item item = items.get(position);
        h.libelle.setText(item.libelle);
        h.lieu.setText(item.lieu);

        // Affiche durée au format "35 min" au lieu de "00:35:00"
        h.duree.setText(formatDuree(item.duree));

        h.itemView.setOnClickListener(v -> listener.onClick(item));
    }

    @Override
    public int getItemCount() { return items.size(); }

    static class VH extends RecyclerView.ViewHolder {
        TextView libelle, lieu, duree;
        VH(View v) {
            super(v);
            libelle = v.findViewById(R.id.recentLibelle);
            lieu    = v.findViewById(R.id.recentLieu);
            duree   = v.findViewById(R.id.recentDuree);
        }
    }

    private static String formatDuree(String time) {
        if (time == null || time.isEmpty()) return "";
        try {
            String[] parts = time.split(":");
            int h = Integer.parseInt(parts[0]);
            int m = Integer.parseInt(parts[1]);
            if (h > 0) return h + "h" + (m > 0 ? String.format("%02d", m) : "");
            return m + " min";
        } catch (Exception e) {
            return time;
        }
    }
}
