package com.example.puy_du_fou.model;

public class CatalogueItem {

    public int    idLieu;
    public String nomLieu;
    public String typeLieu;   // "spectacle", "zone", "restaurant", "hotel", "accueil"

    // Rempli uniquement si un spectacle se déroule à ce lieu
    public Integer idSpectacle        = null;
    public String  libelleSpectacle   = null;
    public String  descriptionSpectacle = null;
    public String  dureeSpectacle     = null;
    public String  dureeAttente       = null;

    public boolean hasSpectacle() {
        return idSpectacle != null;
    }
}
