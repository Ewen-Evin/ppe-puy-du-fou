package com.example.puy_du_fou.model;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class Parcours {

    public static class Etape {
        public int    ordre;
        public int    idSeance;
        public int    idSpectacle;
        public String heureDebut;
        public String heureFin;
        public String heureArrivee;

        public static Etape fromJson(JSONObject o) {
            Etape e = new Etape();
            e.ordre        = o.optInt("ordre");
            e.idSeance     = o.optInt("id_seance");
            e.idSpectacle  = o.optInt("id_spectacle");
            e.heureDebut   = o.optString("heure_debut");
            e.heureFin     = o.optString("heure_fin");
            e.heureArrivee = o.optString("heure_arrivee");
            return e;
        }
    }

    public boolean complet;
    public int     dureeMin;
    public int     attenteMin;
    public List<Etape> etapes = new ArrayList<>();

    public static Parcours fromJson(JSONObject o) {
        Parcours p = new Parcours();
        p.complet    = o.optBoolean("complet");
        p.dureeMin   = o.optInt("duree_totale_min");
        p.attenteMin = o.optInt("attente_min");
        JSONArray arr = o.optJSONArray("etapes");
        if (arr != null) {
            for (int i = 0; i < arr.length(); i++) {
                p.etapes.add(Etape.fromJson(arr.optJSONObject(i)));
            }
        }
        return p;
    }
}
