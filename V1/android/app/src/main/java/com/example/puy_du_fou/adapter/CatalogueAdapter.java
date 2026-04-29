package com.example.puy_du_fou.adapter;

import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.model.CatalogueItem;

import java.util.List;

public class CatalogueAdapter extends RecyclerView.Adapter<CatalogueAdapter.VH> {

    public interface OnSpectacleClick {
        void onClick(CatalogueItem item);
    }

    private final List<CatalogueItem> items;
    private final OnSpectacleClick listener;

    public CatalogueAdapter(List<CatalogueItem> items, OnSpectacleClick listener) {
        this.items    = items;
        this.listener = listener;
    }

    @NonNull
    @Override
    public VH onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_catalogue, parent, false);
        return new VH(v);
    }

    @Override
    public void onBindViewHolder(@NonNull VH h, int position) {
        CatalogueItem item = items.get(position);
        Context ctx = h.itemView.getContext();

        // Barre colorée selon type
        int color = typeColor(item.typeLieu);
        GradientDrawable bar = new GradientDrawable();
        bar.setColor(color);
        h.typeBar.setBackground(bar);

        // Badge type
        String badgeLabel = typeBadgeLabel(item.typeLieu);
        h.typeBadge.setText(badgeLabel);
        GradientDrawable badge = new GradientDrawable();
        badge.setCornerRadius(20f);
        badge.setColor(color);
        h.typeBadge.setBackground(badge);

        h.nomLieu.setText(item.nomLieu);

        if (item.hasSpectacle()) {
            h.libelleSpectacle.setVisibility(View.VISIBLE);
            h.libelleSpectacle.setText(item.libelleSpectacle);
            h.dureeSpectacle.setVisibility(View.VISIBLE);
            h.dureeSpectacle.setText("Durée : " + formatDuree(item.dureeSpectacle)
                    + "  ·  Attente : " + formatDuree(item.dureeAttente));
            h.arrowIcon.setVisibility(View.VISIBLE);
            h.itemView.setOnClickListener(v -> listener.onClick(item));
        } else {
            h.libelleSpectacle.setVisibility(View.GONE);
            h.dureeSpectacle.setVisibility(View.GONE);
            h.arrowIcon.setVisibility(View.GONE);
            h.itemView.setOnClickListener(null);
        }
    }

    @Override
    public int getItemCount() { return items.size(); }

    static class VH extends RecyclerView.ViewHolder {
        View typeBar;
        TextView nomLieu, typeBadge, libelleSpectacle, dureeSpectacle, arrowIcon;

        VH(View v) {
            super(v);
            typeBar           = v.findViewById(R.id.typeBar);
            nomLieu           = v.findViewById(R.id.nomLieu);
            typeBadge         = v.findViewById(R.id.typeBadge);
            libelleSpectacle  = v.findViewById(R.id.libelleSpectacle);
            dureeSpectacle    = v.findViewById(R.id.dureeSpectacle);
            arrowIcon         = v.findViewById(R.id.arrowIcon);
        }
    }

    private static int typeColor(String type) {
        if (type == null) return Color.parseColor("#9E9E9E");
        switch (type) {
            case "spectacle":  return Color.parseColor("#8C1C13");
            case "restaurant": return Color.parseColor("#E65100");
            case "hotel":      return Color.parseColor("#1565C0");
            case "zone":       return Color.parseColor("#2E7D32");
            case "accueil":    return Color.parseColor("#6A1B9A");
            default:           return Color.parseColor("#9E9E9E");
        }
    }

    private static String typeBadgeLabel(String type) {
        if (type == null) return "";
        switch (type) {
            case "spectacle":  return "Spectacle";
            case "restaurant": return "Restaurant";
            case "hotel":      return "Hôtel";
            case "zone":       return "Zone";
            case "accueil":    return "Accueil";
            default:           return type;
        }
    }

    private static String formatDuree(String time) {
        if (time == null || time.isEmpty()) return "—";
        try {
            String[] p = time.split(":");
            int h = Integer.parseInt(p[0]);
            int m = Integer.parseInt(p[1]);
            if (h > 0) return h + "h" + (m > 0 ? String.format("%02d", m) : "");
            return m + " min";
        } catch (Exception e) { return time; }
    }
}
