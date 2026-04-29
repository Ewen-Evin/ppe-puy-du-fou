package com.example.puy_du_fou.api;

import android.content.Context;
import android.os.Handler;
import android.os.Looper;

import com.example.puy_du_fou.R;
import com.example.puy_du_fou.util.Session;

import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * Wrapper HTTP minimal pour l'API REST PPE Puy du Fou.
 * Utilise HttpURLConnection (built-in Android, zéro dépendance).
 * Toutes les requêtes sont exécutées sur un thread d'arrière-plan,
 * le callback est invoqué sur le main thread.
 */
public class ApiClient {

    public interface Callback {
        void onResult(ApiResponse response);
    }

    private static final ExecutorService EXECUTOR = Executors.newFixedThreadPool(4);
    private static final Handler MAIN = new Handler(Looper.getMainLooper());

    private final String baseUrl;
    private final Session session;

    public ApiClient(Context ctx) {
        this.baseUrl = ctx.getString(R.string.api_base_url);
        this.session = new Session(ctx);
    }

    public void get(String path, Callback cb) {
        execute("GET", path, null, cb);
    }

    public void post(String path, String jsonBody, Callback cb) {
        execute("POST", path, jsonBody, cb);
    }

    public void put(String path, String jsonBody, Callback cb) {
        execute("PUT", path, jsonBody, cb);
    }

    public void delete(String path, Callback cb) {
        execute("DELETE", path, null, cb);
    }

    private void execute(String method, String path, String body, Callback cb) {
        EXECUTOR.execute(() -> {
            ApiResponse result;
            HttpURLConnection conn = null;
            try {
                URL url = new URL(baseUrl + path);
                conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod(method);
                conn.setConnectTimeout(10000);
                conn.setReadTimeout(15000);
                conn.setRequestProperty("Accept", "application/json");
                conn.setRequestProperty("Content-Type", "application/json; charset=utf-8");

                String token = session.getToken();
                if (token != null && !token.isEmpty()) {
                    conn.setRequestProperty("Authorization", "Bearer " + token);
                }

                if (body != null) {
                    conn.setDoOutput(true);
                    try (OutputStream os = conn.getOutputStream()) {
                        os.write(body.getBytes(StandardCharsets.UTF_8));
                    }
                }

                int code = conn.getResponseCode();
                InputStream is = (code >= 200 && code < 300) ? conn.getInputStream() : conn.getErrorStream();
                String responseBody = readAll(is);
                result = new ApiResponse(code, responseBody, null);
            } catch (Exception e) {
                result = new ApiResponse(0, null, e.getMessage());
            } finally {
                if (conn != null) conn.disconnect();
            }
            final ApiResponse fr = result;
            MAIN.post(() -> cb.onResult(fr));
        });
    }

    private static String readAll(InputStream is) throws Exception {
        if (is == null) return "";
        StringBuilder sb = new StringBuilder();
        try (BufferedReader br = new BufferedReader(new InputStreamReader(is, StandardCharsets.UTF_8))) {
            String line;
            while ((line = br.readLine()) != null) sb.append(line);
        }
        return sb.toString();
    }
}
