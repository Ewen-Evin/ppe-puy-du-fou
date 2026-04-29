package com.example.puy_du_fou.model;

import org.json.JSONObject;

public class Spectacle {
    public int id;
    public String libelle;
    public String description;
    public String dureeSpectacle;
    public String dureeAttente;
    public String lieuNom;
    public String heureDebut; // renseigné quand construit depuis une séance

    public static Spectacle fromJson(JSONObject o) {
        Spectacle s = new Spectacle();
        s.id             = o.optInt("id_spectacle");
        s.libelle        = o.optString("libelle");
        s.description    = o.optString("description");
        s.dureeSpectacle = o.optString("duree_spectacle");
        s.dureeAttente   = o.optString("duree_attente");
        s.lieuNom        = o.optString("lieu_nom");
        s.heureDebut     = o.optString("heure_debut");
        return s;
    }
}
