package com.example.puy_du_fou.util;

import android.app.Activity;
import android.content.Intent;

import com.example.puy_du_fou.CarteActivity;
import com.example.puy_du_fou.CatalogueActivity;
import com.example.puy_du_fou.HistoriqueActivity;
import com.example.puy_du_fou.MainActivity;
import com.example.puy_du_fou.ProfileActivity;
import com.example.puy_du_fou.R;
import com.google.android.material.bottomnavigation.BottomNavigationView;

/**
 * Configure la BottomNavigationView identique sur toutes les activités principales.
 * 4 onglets : Accueil, Catalogue, Historique, Profil.
 */
public class NavHelper {

    public static void setup(Activity activity, BottomNavigationView nav, int selectedItemId) {
        // Listener en premier, puis sélection — évite de déclencher une navigation parasite
        nav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            if (id == selectedItemId) return true;

            Class<?> target = null;
            if      (id == R.id.nav_home)       target = MainActivity.class;
            else if (id == R.id.nav_catalogue)  target = CatalogueActivity.class;
            else if (id == R.id.nav_historique) target = HistoriqueActivity.class;
            else if (id == R.id.nav_profil)     target = ProfileActivity.class;
            else if (id == R.id.nav_carte)      target = CarteActivity.class;

            if (target != null) {
                Intent intent = new Intent(activity, target);
                intent.addFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
                activity.startActivity(intent);
                activity.overridePendingTransition(0, 0);
            }
            return true;
        });
        nav.setSelectedItemId(selectedItemId);
    }

    /** À appeler dans onResume() pour corriger la sélection après retour sur l'activité. */
    public static void syncSelection(Activity activity, int selectedItemId) {
        BottomNavigationView nav = activity.findViewById(R.id.bottomNav);
        if (nav != null && nav.getSelectedItemId() != selectedItemId) {
            nav.setSelectedItemId(selectedItemId);
        }
    }
}
