// Configuration API de base
const API_BASE_URL = 'http://localhost:8016/api';

// État global de l'application
window.appState = {
  isAuthenticated: false,
  user: null,
  token: null,
  mobileMenuOpen: false
};

// Utilitaires pour l'authentification
window.auth = {
  // Récupérer le token depuis localStorage
  getToken() {
    return localStorage.getItem('unescopilot_token');
  },

  // Sauvegarder le token
  setToken(token) {
    localStorage.setItem('unescopilot_token', token);
    window.appState.token = token;
    window.appState.isAuthenticated = true;
  },

  // Supprimer le token
  removeToken() {
    localStorage.removeItem('unescopilot_token');
    window.appState.token = null;
    window.appState.isAuthenticated = false;
    window.appState.user = null;
  },

  // Vérifier si l'utilisateur est connecté
  isLoggedIn() {
    const token = this.getToken();
    if (token) {
      window.appState.token = token;
      window.appState.isAuthenticated = true;
      return true;
    }
    return false;
  },

  // Déconnexion
  logout() {
    this.removeToken();
    window.location.href = 'login.html';
  }
};

// Utilitaires pour les appels API
window.api = {
  // Faire un appel API avec authentification
  async request(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    const token = window.auth.getToken();

    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json',
        ...(token && { 'Authorization': `Bearer ${token}` })
      }
    };

    const config = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers
      }
    };

    try {
      const response = await fetch(url, config);

      // Gérer les erreurs d'authentification
      if (response.status === 401) {
        window.auth.logout();
        return;
      }

      if (!response.ok) {
        // Créer une erreur avec plus d'informations
        const errorData = await response.json().catch(() => ({}));
        const error = new Error(`HTTP error! status: ${response.status}`);
        error.status = response.status;
        error.data = errorData;
        throw error;
      }

      // Si la réponse est 204 No Content, pas de JSON à parser
      if (response.status === 204) {
        return null;
      }

      return await response.json();
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  },

  // Méthodes spécifiques
  async login(credentials) {
    return this.request('/security/login', {
      method: 'POST',
      body: JSON.stringify(credentials)
    });
  },

  async register(userData) {
    return this.request('/security/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  },

  async getProfile() {
    return this.request('/me');
  },

  async updateProfile(userData) {
    return this.request('/me', {
      method: 'PUT',
      body: JSON.stringify(userData)
    });
  },

  async getSites(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const endpoint = queryString ? `/sites?${queryString}` : '/sites';
    return this.request(endpoint);
  },

  async getSite(id) {
    return this.request(`/sites/${id}`);
  },

  async getVisits(type = null) {
    const params = type ? { type } : {};
    const queryString = new URLSearchParams(params).toString();
    const endpoint = queryString ? `/visits?${queryString}` : '/visits';
    return this.request(endpoint);
  },

  async addVisit(siteId, type, visitedAt = null) {
    return this.request('/visits', {
      method: 'POST',
      body: JSON.stringify({ siteId, type, visitedAt })
    });
  },

  async removeVisit(visitId) {
    return this.request(`/visits/${visitId}`, {
      method: 'DELETE'
    });
  },

  async getRanking() {
    return this.request('/ranking');
  }
};

/**
 * Charge un composant HTML depuis une URL et l'injecte dans un élément du DOM.
 * @param {HTMLElement} element - L'élément DOM où le contenu doit être injecté.
 * @param {string} component - Le nom du composant à charger (sans extension).
 * ex: <div x-init="loadComponent($el, 'header')"></div>
 */
async function loadComponent(element, component) {
    try {
        const basePath = '/components/';

        const response = await fetch(basePath + component + '.html');
        if (!response.ok) {
            throw new Error(`Échec du chargement du composant ${component}: ${response.statusText}`);
        }
        element.innerHTML = await response.text();
        // Dans Alpine.js v3+, la détection et l'initialisation des nouveaux composants
        // ajoutés au DOM se fait automatiquement si l'élément parent est déjà sous
    } catch (error) {
        console.error('Erreur lors du chargement du composant :', error);
        element.innerHTML = `<p style="color: red;">Échec du chargement du composant.</p>`; // Message d'erreur simple pour l'utilisateur
    }
}
// Utilitaires généraux
window.utils = {
  // Afficher un message toast
  showToast(message, type = 'info') {
    // TODO: Implémenter un système de notifications toast
    console.log(`Toast ${type}: ${message}`);
  },

  // Formater une date
  formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR');
  },

  // Tronquer un texte
  truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
  },

  // Décoder les entités HTML
  decodeHtmlEntities(text) {
    if (!text) return text;
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
  }
};

// Initialisation globale
document.addEventListener('DOMContentLoaded', function() {
  // Vérifier l'authentification au chargement
  window.auth.isLoggedIn();

  // Enregistrer le service worker pour la PWA
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
      .then(registration => {
        console.log('Service Worker enregistré avec succès:');
      })
      .catch(error => {
        console.log("Échec de l'enregistrement du Service Worker:", error);
      });
  }
});

// Fonctions globales pour Alpine.js
window.logout = function() {
  window.auth.logout();
};

window.isAuthenticated = function() {
  return window.appState.isAuthenticated;
};
