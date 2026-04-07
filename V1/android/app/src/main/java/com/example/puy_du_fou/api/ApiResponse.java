package com.example.puy_du_fou.api;

/**
 * Conteneur générique pour une réponse de l'API REST.
 *  - status : code HTTP (ou 0 si erreur réseau)
 *  - body   : corps de la réponse en JSON brut (peut être null)
 *  - error  : message d'erreur si applicable
 */
public class ApiResponse {
    public final int status;
    public final String body;
    public final String error;

    public ApiResponse(int status, String body, String error) {
        this.status = status;
        this.body   = body;
        this.error  = error;
    }

    public boolean isSuccess() {
        return status >= 200 && status < 300;
    }
}
