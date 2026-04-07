package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.util.Session;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        Session session = new Session(this);
        if (!session.isLoggedIn()) {
            startActivity(new Intent(this, LoginActivity.class));
            finish();
            return;
        }

        TextView welcome = findViewById(R.id.welcomeText);
        welcome.setText("Bienvenue " + session.getPrenom());

        findViewById(R.id.btnSpectacles).setOnClickListener(v ->
                startActivity(new Intent(this, SpectaclesActivity.class)));
        findViewById(R.id.btnHistorique).setOnClickListener(v ->
                startActivity(new Intent(this, HistoriqueActivity.class)));
        findViewById(R.id.btnLogout).setOnClickListener(v -> {
            session.clear();
            startActivity(new Intent(this, LoginActivity.class));
            finish();
        });
    }
}
