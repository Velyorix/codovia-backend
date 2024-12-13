# **Documentation API Complète**

### **Base URL**: `http://127.0.0.1:8000/api`

---

## **Authentification**

### **1. Inscription d'un utilisateur**
- **URL**: `/register`
- **Method**: `POST`
- **Description**: Inscription d'un nouvel utilisateur.
- **Body**:
  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Réponse**:
    - **201 Created**
      ```json
      {
        "token": "string"
      }
      ```

### **2. Connexion**
- **URL**: `/login`
- **Method**: `POST`
- **Description**: Connexion d'un utilisateur.
- **Body**:
  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```
- **Réponse**:
    - **200 OK**
      ```json
      {
        "token": "string"
      }
      ```

### **3. Déconnexion**
- **URL**: `/logout`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Déconnexion de l'utilisateur.
- **Réponse**:
    - **204 No Content**

### **4. Envoi de notification de vérification d'email**
- **URL**: `/email/verification-notification`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Envoie un email pour vérifier l'adresse.
- **Réponse**:
    - **200 OK**
      ```json
      {
        "message": "Verification email sent."
      }
      ```

### **5. Vérification d'email**
- **URL**: `/email/verify/{id}/{hash}`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Vérifie l'email de l'utilisateur.
- **Réponse**:
    - **200 OK**
      ```json
      {
        "message": "Email verified successfully."
      }
      ```

---

## **Articles**

### **1. Liste des articles publics**
- **URL**: `/articles`
- **Method**: `GET`
- **Description**: Liste paginée des articles publics.
- **Query Parameters**:
    - `per_page`: Nombre d'articles par page.
- **Réponse**:
    - **200 OK**
      ```json
      {
        "data": [
          {
            "id": "integer",
            "title": "string",
            "content": "string",
            "status": "public",
            "tags": ["array"],
            "category": {
              "id": "integer",
              "name": "string"
            }
          }
        ],
        "meta": {
          "pagination": {
            "total": "integer",
            "count": "integer",
            "per_page": "integer",
            "current_page": "integer",
            "total_pages": "integer"
          }
        }
      }
      ```

### **2. Rechercher des articles**
- **URL**: `/articles/search`
- **Method**: `GET`
- **Description**: Recherche d'articles.
- **Query Parameters**:
    - `query`: Mot-clé.
    - `category`: Filtrer par catégorie.
    - `date_from`: Date de début.
    - `date_to`: Date de fin.
- **Réponse**: Même réponse que la liste des articles publics.

### **3. Détails d'un article**
- **URL**: `/articles/{article}`
- **Method**: `GET`
- **Description**: Récupère les détails d'un article spécifique.
- **Réponse**:
    - **200 OK**
      ```json
      {
        "id": "integer",
        "title": "string",
        "content": "string",
        "tags": ["array"]
      }
      ```

### **4. Création d'un article**
- **URL**: `/articles`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Création d'un nouvel article.
- **Body**:
  ```json
  {
    "title": "string",
    "content": "string",
    "category_id": "integer",
    "tags": ["array"]
  }
  ```

---

## **Modération des Articles**

### **1. Liste des articles en revue**
- **URL**: `/admin/articles/under_review`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Liste paginée des articles en statut `under_review`.
- **Réponse**:
    - **200 OK**: Même structure que la liste des articles.

### **2. Signaler un article**
- **URL**: `/articles/{article}/flag`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Marquer un article comme `under_review`.

### **3. Résoudre un article signalé**
- **URL**: `/admin/articles/{article}/resolve`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Description**: Publier un article précédemment en revue.
- **Réponse**:
    - **200 OK**
      ```json
      {
        "message": "Article reviewed and published."
      }
      ```

---

## **Catégories**

### **1. Liste des catégories**
- **URL**: `/categories`
- **Method**: `GET`
- **Description**: Récupère toutes les catégories.

### **2. Ajouter une catégorie**
- **URL**: `/categories`
- **Method**: `POST`
- **Headers**: `Authorization: Bearer {token}`
- **Body**:
  ```json
  {
    "name": "string",
    "description": "string",
    "parent_id": "integer"
  }
  ```

---

## **Commentaires**

### **1. Liste des commentaires**
- **URL**: `/articles/{article}/comments`
- **Method**: `GET`
- **Description**: Liste des commentaires pour un article.

---

## **Favoris**

### **1. Vérifier si un article est favori**
- **URL**: `/articles/{article}/is-favorite`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Réponse**:
    - **200 OK**
      ```json
      {
        "is_favorite": true
      }
      ```

---

## **Sanctions**

### **1. Vérifier le statut de sanction d'un utilisateur**
- **URL**: `/admin/users/{user}/sanction-status`
- **Method**: `GET`
- **Headers**: `Authorization: Bearer {token}`
- **Réponse**:
    - **200 OK**
      ```json
      {
        "is_sanctioned": true
      }
      ```
