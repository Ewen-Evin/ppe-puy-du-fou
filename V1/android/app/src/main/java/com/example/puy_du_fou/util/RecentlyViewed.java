package com.example.puy_du_fou.util;

import android.content.Context;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Stocke localement les 5 derniers spectacles consultés (SharedPreferences).
 */
public class RecentlyViewed {

    private static final String PREF = "ppe_recent";
    private static final String KEY  = "spectacles";
    private static final int    MAX  = 5;

    public static class Item {
        public int    id;
        public String libelle;
        public String description;
        public String duree;
        public String attente;
        public String lieu;
    }

    public static void save(Context ctx, int id, String libelle,
                            String description, String duree, String attente, String lieu) {
        List<Item> list = load(ctx);
        list.removeIf(i -> i.id == id);

        Item item = new Item();
        item.id          = id;
        item.libelle     = libelle;
        item.description = description != null ? description : "";
        item.duree       = duree   != null ? duree   : "";
        item.attente     = attente != null ? attente : "";
        item.lieu        = lieu    != null ? lieu    : "";
        list.add(0, item);

        if (list.size() > MAX) list = list.subList(0, MAX);

        JSONArray arr = new JSONArray();
        for (Item i : list) {
            try {
                arr.put(new JSONObject()
                        .put("id", i.id)
                        .put("libelle", i.libelle)
                        .put("description", i.description)
                        .put("duree", i.duree)
                        .put("attente", i.attente)
                        .put("lieu", i.lieu));
            } catch (Exception ignored) {}
        }
        ctx.getSharedPreferences(PREF, Context.MODE_PRIVATE)
           .edit().putString(KEY, arr.toString()).apply();
    }

    public static List<Item> load(Context ctx) {
        String json = ctx.getSharedPreferences(PREF, Context.MODE_PRIVATE)
                         .getString(KEY, "[]");
        List<Item> list = new ArrayList<>();
        try {
            JSONArray arr = new JSONArray(json);
            for (int i = 0; i < arr.length(); i++) {
                JSONObject o = arr.getJSONObject(i);
                Item item   = new Item();
                item.id          = o.getInt("id");
                item.libelle     = o.optString("libelle");
                item.description = o.optString("description");
                item.duree       = o.optString("duree");
                item.attente     = o.optString("attente");
                item.lieu        = o.optString("lieu");
                list.add(item);
            }
        } catch (Exception ignored) {}
        return list;
    }
}
