package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.widget.SeekBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.NavHelper;
import com.example.puy_du_fou.util.Session;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.button.MaterialButton;

import org.json.JSONObject;

import java.util.Locale;

public class ProfileActivity extends AppCompatActivity {

    private Session session;
    private SeekBar seekVitesse;
    private TextView vitesseValeur;
    private double vitesseCourante;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        session = new Session(this);

        NavHelper.setup(this, (BottomNavigationView) findViewById(R.id.bottomNav),
                R.id.nav_profil);

        // ── En-tête ──────────────────────────────────────────────────────────
        String prenom = session.getPrenom();
        String nom    = session.getNom();

        String initiales = "";
        if (!prenom.isEmpty()) initiales += prenom.charAt(0);
        if (!nom.isEmpty())    initiales += nom.charAt(0);
        ((TextView) findViewById(R.id.avatarText)).setText(initiales.toUpperCase(Locale.FRANCE));
        ((TextView) findViewById(R.id.nomComplet)).setText(prenom + " " + nom);
        ((TextView) findViewById(R.id.emailText)).setText(session.getEmail());

        // Badge type de profil
        String type = session.getTypeProfil();
        String badgeLabel = "gestionnaire".equals(type) ? "Gestionnaire" : "Visiteur";
        ((TextView) findViewById(R.id.badgeProfil)).setText(badgeLabel);

        // ── Infos compte ─────────────────────────────────────────────────────
        ((TextView) findViewById(R.id.infoNom)).setText(nom);
        ((TextView) findViewById(R.id.infoPrenom)).setText(prenom);
        ((TextView) findViewById(R.id.infoEmail)).setText(session.getEmail());
        ((TextView) findViewById(R.id.infoProfil)).setText(badgeLabel);

        // ── Vitesse de marche ─────────────────────────────────────────────────
        vitesseCourante = session.getVitesse();
        vitesseValeur   = findViewById(R.id.vitesseValeur);
        seekVitesse     = findViewById(R.id.seekVitesse);

        // SeekBar : 0-10 → 2.0-7.0 km/h (pas de 0.5)
        seekVitesse.setMax(10);
        seekVitesse.setProgress(vitesseToProgress(vitesseCourante));
        afficherVitesse(vitesseCourante);

        seekVitesse.setOnSeekBarChangeListener(new SeekBar.OnSeekBarChangeListener() {
            @Override
            public void onProgressChanged(SeekBar sb, int progress, boolean fromUser) {
                vitesseCourante = progressToVitesse(progress);
                afficherVitesse(vitesseCourante);
            }
            @Override public void onStartTrackingTouch(SeekBar sb) {}
            @Override public void onStopTrackingTouch(SeekBar sb) {}
        });

        MaterialButton btnSave = findViewById(R.id.btnSauvegarder);
        btnSave.setOnClickListener(v -> sauvegarderVitesse(btnSave));

        // ── Déconnexion ───────────────────────────────────────────────────────
        ((MaterialButton) findViewById(R.id.btnLogout)).setOnClickListener(v -> {
            session.clear();
            Intent it = new Intent(this, LoginActivity.class);
            it.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(it);
        });
    }

    private void sauvegarderVitesse(MaterialButton btn) {
        btn.setEnabled(false);
        btn.setText("Sauvegarde…");
        try {
            JSONObject body = new JSONObject().put("vitesse_marche", vitesseCourante);
            new ApiClient(this).put("/api/auth/vitesse", body.toString(), resp -> {
                btn.setEnabled(true);
                btn.setText("Sauvegarder");
                if (resp.isSuccess()) {
                    // Met à jour la session locale
                    session.save(session.getToken(), session.getUserId(), session.getEmail(),
                            session.getNom(), session.getPrenom(), vitesseCourante);
                    Toast.makeText(this, "Vitesse mise à jour ✓", Toast.LENGTH_SHORT).show();
                } else {
                    Toast.makeText(this, "Erreur lors de la sauvegarde", Toast.LENGTH_SHORT).show();
                }
            });
        } catch (Exception e) {
            btn.setEnabled(true);
            btn.setText("Sauvegarder");
        }
    }

    // ── Helpers vitesse ───────────────────────────────────────────────────────

    /** progress 0-10 → vitesse 2.0-7.0 km/h */
    private static double progressToVitesse(int progress) {
        return 2.0 + progress * 0.5;
    }

    /** vitesse 2.0-7.0 → progress 0-10 */
    private static int vitesseToProgress(double vitesse) {
        int p = (int) Math.round((vitesse - 2.0) / 0.5);
        return Math.max(0, Math.min(10, p));
    }

    private void afficherVitesse(double v) {
        vitesseValeur.setText(String.format(Locale.FRANCE, "%.1f km/h", v));
    }

    // ─────────────────────────────────────────────────────────────────────────

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
