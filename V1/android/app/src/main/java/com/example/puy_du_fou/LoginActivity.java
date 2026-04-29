package com.example.puy_du_fou;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.api.ApiClient;
import com.example.puy_du_fou.util.Session;

import org.json.JSONObject;

public class LoginActivity extends AppCompatActivity {

    private EditText emailInput, passwordInput;
    private Button loginButton;
    private TextView registerLink;
    private ProgressBar progress;

    private ApiClient api;
    private Session session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        api     = new ApiClient(this);
        session = new Session(this);

        if (session.isLoggedIn()) {
            goToMain();
            return;
        }

        emailInput    = findViewById(R.id.emailInput);
        passwordInput = findViewById(R.id.passwordInput);
        loginButton   = findViewById(R.id.loginButton);
        registerLink  = findViewById(R.id.registerLink);
        progress      = findViewById(R.id.progress);

        loginButton.setOnClickListener(v -> doLogin());
        registerLink.setOnClickListener(v ->
                startActivity(new Intent(this, RegisterActivity.class)));
    }

    private void doLogin() {
        String email = emailInput.getText().toString().trim();
        String pwd   = passwordInput.getText().toString();
        if (email.isEmpty() || pwd.isEmpty()) {
            Toast.makeText(this, "Renseigne email et mot de passe", Toast.LENGTH_SHORT).show();
            return;
        }
        progress.setVisibility(View.VISIBLE);
        loginButton.setEnabled(false);

        try {
            JSONObject body = new JSONObject()
                    .put("email", email)
                    .put("mot_de_passe", pwd);

            api.post("/api/auth/login", body.toString(), resp -> {
                progress.setVisibility(View.GONE);
                loginButton.setEnabled(true);
                if (!resp.isSuccess() || resp.body == null) {
                    Toast.makeText(this, "Identifiants invalides", Toast.LENGTH_SHORT).show();
                    return;
                }
                try {
                    JSONObject json = new JSONObject(resp.body);
                    String token = json.getString("token");
                    JSONObject u = json.getJSONObject("user");
                    if (!"visiteur".equals(u.optString("type_profil"))) {
                        Toast.makeText(this, "Compte non visiteur", Toast.LENGTH_SHORT).show();
                        return;
                    }
                    session.save(
                            token,
                            u.getInt("id_utilisateur"),
                            u.optString("email"),
                            u.optString("nom"),
                            u.optString("prenom"),
                            u.optDouble("vitesse_marche", 4.0)
                    );
                    goToMain();
                } catch (Exception e) {
                    Toast.makeText(this, "Erreur réponse serveur", Toast.LENGTH_SHORT).show();
                }
            });
        } catch (Exception e) {
            progress.setVisibility(View.GONE);
            loginButton.setEnabled(true);
        }
    }

    private void goToMain() {
        startActivity(new Intent(this, MainActivity.class));
        finish();
    }
}
