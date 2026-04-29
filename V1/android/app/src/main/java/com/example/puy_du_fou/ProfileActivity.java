package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.util.NavHelper;
import com.example.puy_du_fou.util.Session;
import com.google.android.material.bottomnavigation.BottomNavigationView;

import java.util.Locale;

public class ProfileActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        Session session = new Session(this);

        // Navbar
        BottomNavigationView nav = findViewById(R.id.bottomNav);
        NavHelper.setup(this, nav, R.id.nav_profil);

        // Avatar initiales
        String prenom = session.getPrenom();
        String nom    = session.getNom();
        String initiales = "";
        if (!prenom.isEmpty()) initiales += prenom.charAt(0);
        if (!nom.isEmpty())    initiales += nom.charAt(0);
        ((TextView) findViewById(R.id.avatarText)).setText(initiales.toUpperCase());

        // Nom complet
        ((TextView) findViewById(R.id.nomComplet)).setText(prenom + " " + nom);

        // Email
        ((TextView) findViewById(R.id.emailText)).setText(session.getEmail());

        // Vitesse de marche
        double vitesse = session.getVitesse();
        ((TextView) findViewById(R.id.vitesseText)).setText(
                String.format(Locale.FRANCE, "%.1f km/h", vitesse)
        );

        // Déconnexion
        ((Button) findViewById(R.id.btnLogout)).setOnClickListener(v -> {
            session.clear();
            Intent intent = new Intent(this, LoginActivity.class);
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        NavHelper.syncSelection(this, R.id.nav_profil);
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
    }
}
