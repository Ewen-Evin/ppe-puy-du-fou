package com.example.puy_du_fou.util;

import android.content.Context;
import android.content.SharedPreferences;

/**
 * Stockage local du JWT et des infos utilisateur (SharedPreferences).
 */
public class Session {

    private static final String PREF      = "ppe_session";
    private static final String K_TOKEN   = "token";
    private static final String K_USER_ID = "user_id";
    private static final String K_EMAIL   = "email";
    private static final String K_NOM     = "nom";
    private static final String K_PRENOM  = "prenom";
    private static final String K_VITESSE = "vitesse_marche";

    private final SharedPreferences prefs;

    public Session(Context ctx) {
        this.prefs = ctx.getApplicationContext().getSharedPreferences(PREF, Context.MODE_PRIVATE);
    }

    public void save(String token, int userId, String email, String nom, String prenom, double vitesse) {
        prefs.edit()
                .putString(K_TOKEN, token)
                .putInt(K_USER_ID, userId)
                .putString(K_EMAIL, email)
                .putString(K_NOM, nom)
                .putString(K_PRENOM, prenom)
                .putFloat(K_VITESSE, (float) vitesse)
                .apply();
    }

    public String getToken()  { return prefs.getString(K_TOKEN, null); }
    public int    getUserId() { return prefs.getInt(K_USER_ID, 0); }
    public String getEmail()  { return prefs.getString(K_EMAIL, ""); }
    public String getNom()    { return prefs.getString(K_NOM, ""); }
    public String getPrenom() { return prefs.getString(K_PRENOM, ""); }
    public double getVitesse(){ return prefs.getFloat(K_VITESSE, 4.0f); }

    public boolean isLoggedIn() {
        String t = getToken();
        return t != null && !t.isEmpty();
    }

    public void clear() {
        prefs.edit().clear().apply();
    }
}
