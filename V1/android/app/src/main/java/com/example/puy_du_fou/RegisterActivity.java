package com.example.puy_du_fou;

import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.example.puy_du_fou.api.ApiClient;

import org.json.JSONObject;

public class RegisterActivity extends AppCompatActivity {

    private EditText emailInput, passwordInput, nomInput, prenomInput, vitesseInput;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        androidx.appcompat.widget.Toolbar tb = findViewById(R.id.toolbar);
        tb.setNavigationOnClickListener(v -> finish());

        emailInput    = findViewById(R.id.emailInput);
        passwordInput = findViewById(R.id.passwordInput);
        nomInput      = findViewById(R.id.nomInput);
        prenomInput   = findViewById(R.id.prenomInput);
        vitesseInput  = findViewById(R.id.vitesseInput);
        Button btn    = findViewById(R.id.registerButton);

        ApiClient api = new ApiClient(this);

        btn.setOnClickListener(v -> {
            try {
                JSONObject body = new JSONObject()
                        .put("email", emailInput.getText().toString().trim())
                        .put("mot_de_passe", passwordInput.getText().toString())
                        .put("nom", nomInput.getText().toString().trim())
                        .put("prenom", prenomInput.getText().toString().trim())
                        .put("vitesse_marche",
                                vitesseInput.getText().toString().isEmpty()
                                        ? 4.0
                                        : Double.parseDouble(vitesseInput.getText().toString()));
                api.post("/api/auth/register", body.toString(), resp -> {
                    if (resp.isSuccess()) {
                        Toast.makeText(this, "Compte créé, connecte-toi", Toast.LENGTH_SHORT).show();
                        finish();
                    } else {
                        Toast.makeText(this, "Erreur création", Toast.LENGTH_SHORT).show();
                    }
                });
            } catch (Exception e) {
                Toast.makeText(this, "Champs invalides", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
