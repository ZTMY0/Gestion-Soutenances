import csv
import random
import string
import secrets

# ================= CONFIGURATION =================
DOMAIN_ETUDIANT = "eidia.ueuromed.org"
DOMAIN_PROF = "prof.ueuromed.org"

# Configuration des effectifs
CONFIG_ETUDIANTS = {
    "Cybersecurite": 67, "AI": 55, "BigData": 45, "Robotique": 35, "Fullstack": 25
}
NB_PROFS = 25
FILIERES_PROFS = ["CYBER", "BIGDATA", "AI", "ROBO", "FULL"]

# Mapping IDs SQL
FILIERE_IDS = {
    "Cybersecurite": 1, "CYBER": 1,
    "AI": 2,            "AI": 2,
    "BigData": 3,       "BIGDATA": 3,
    "Robotique": 4,     "ROBO": 4,
    "Fullstack": 5,     "FULL": 5
}

# --- BANQUES DE NOMS ---
PRENOMS = [
    "Amine", "Sarah", "Mohamed", "Salma", "Youssef", "Hajar", "Omar", "Kenza", "Mehdi", "Zineb",
    "Anas", "Rania", "Hamza", "Sofia", "Karim", "Noura", "Walid", "Rim", "Oussama", "Ghita",
    "Saad", "Aya", "Taha", "Manal", "Yassine", "Asmaa", "Bilal", "Kaoutar", "Nizar", "Meryem",
    "Ali", "Fatima", "Hassan", "Imane", "Driss", "Lamia", "Reda", "Sanae", "Ismail", "Houda",
    "Ahmed", "Khalid", "Samir", "Latifa", "Brahim", "Najat", "Rachid"
]

NOMS = [
    "Berrada", "Fassi", "Benjelloun", "Chraibi", "El Amrani", "Daoudi", "Mernissi", "Tazi",
    "Bennani", "Idrissi", "Alami", "Mansouri", "Filali", "Naciri", "Guedira", "Benali",
    "Kabbaj", "Slaoui", "Ouazzani", "Tahiri", "Kadiri", "Zerrad", "Raiss", "Talbi", "Chaoui",
    "Zouhair", "Sefrioui", "Jettou", "Lahlou", "Bennis", "Guessous"
]

# Votre équipe
COLLEGUES_CYBER = [
    ["Ihab", "Zaghdane"], ["Abdelmoughit", "Mossaid"], 
    ["Nizar", "Zouizra"], ["Nourddine", "Kissiri"]
]

GLOBAL_USED_LOGINS = set()

# ================= FONCTIONS =================

def generate_simple_password(length=8):
    """ Mot de passe alphanumérique de 8 caractères (Lettres + Chiffres) """
    alphabet = string.ascii_letters + string.digits
    return ''.join(secrets.choice(alphabet) for i in range(length))

def get_login_base(prenom, nom, is_prof=False):
    """ Génère un login unique (prenom.nom) """
    base = f"{prenom.lower()}.{nom.lower().replace(' ', '')}"
    login = base
    counter = 1
    
    while login in GLOBAL_USED_LOGINS:
        if is_prof:
            variants = [
                f"{prenom.lower()[0]}.{nom.lower()}", 
                f"{nom.lower()}.{prenom.lower()}", 
                f"{prenom.lower()}{nom.lower()}"
            ]
            found = False
            for v in variants:
                if v not in GLOBAL_USED_LOGINS:
                    login = v
                    found = True
                    break
            if not found: return None 
        else:
            counter += 1
            login = f"{base}{counter}"
            
    return login

# ================= GÉNÉRATEURS =================

def generate_students():
    print("🔹 Génération des Étudiants...")
    
    for filiere, count in CONFIG_ETUDIANTS.items():
        rows = []
        filiere_id = FILIERE_IDS.get(filiere, 1)

        # 1. Équipe PFE
        if filiere == "Cybersecurite":
            for p, n in COLLEGUES_CYBER:
                login = get_login_base(p, n)
                GLOBAL_USED_LOGINS.add(login)
                email = f"{login}@{DOMAIN_ETUDIANT}"
                
                # Format: Nom;Prenom;Email;Login;Password;Role;Filiere_ID
                rows.append([n, p, email, login, generate_simple_password(), 'etudiant', filiere_id])

        # 2. Remplissage aléatoire
        while len(rows) < count:
            p = secrets.choice(PRENOMS)
            n = secrets.choice(NOMS)
            
            login = get_login_base(p, n)
            GLOBAL_USED_LOGINS.add(login)
            email = f"{login}@{DOMAIN_ETUDIANT}"
            
            rows.append([n, p, email, login, generate_simple_password(), 'etudiant', filiere_id])

        filename = f"etudiants_{filiere.lower()}.csv"
        with open(filename, 'w', newline='', encoding='utf-8') as f:
            writer = csv.writer(f, delimiter=';')
            # Pas d'entête CSV pour import direct SQL facile
            writer.writerows(rows)
        print(f"   ✅ {filename} ({len(rows)} étudiants)")

def generate_profs():
    print("🔹 Génération des Professeurs...")
    rows = []
    attempts = 0
    
    while len(rows) < NB_PROFS and attempts < 5000:
        attempts += 1
        p = secrets.choice(PRENOMS)
        n = secrets.choice(NOMS)
        
        login = get_login_base(p, n, is_prof=True)
        if not login: continue
            
        GLOBAL_USED_LOGINS.add(login)
        email = f"{login}@{DOMAIN_PROF}"
        
        main_spec = secrets.choice(FILIERES_PROFS)
        other_specs = [s for s in FILIERES_PROFS if s != main_spec]
        specialites_str = f"{main_spec}, {', '.join(random.sample(other_specs, 1))}"
        
        # Format: Nom;Prenom;Email;Login;Password;Role;Specialite
        rows.append([n, p, email, login, generate_simple_password(), 'prof', specialites_str])

    filename = "profs_eidia.csv"
    with open(filename, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f, delimiter=';')
        writer.writerows(rows)
    print(f"   ✅ {filename} ({len(rows)} profs)")

# ================= MAIN =================
if __name__ == "__main__":
    GLOBAL_USED_LOGINS.clear()
    generate_students()
    generate_profs()
    print("\n🚀 Fichiers CSV générés !")
    print("Chaque fichier contient maintenant 7 colonnes :")
    print("1. Nom")
    print("2. Prénom")
    print("3. Email (prenom.nom@ueuromed.org)")
    print("4. Login (prenom.nom)")
    print("5. Password (8 chars)")
    print("6. Rôle")
    print("7. Filière ID (Étudiants) OU Spécialité (Profs)")