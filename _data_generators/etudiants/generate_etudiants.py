import csv
import random
import string

# 1. Configuration
DOMAIN_EMAIL = "eidia.ueuromed.org"

CONFIG_FILIERES = {
    "Cybersecurite": 67,
    "AI": 55,
    "BigData": 45,
    "Robotique": 35,
    "Fullstack": 25
}

# Tes collègues (Cyber)
COLLEGUES = [
    ["Ihab", "Zaghdane"],
    ["Abdelmoughit", "Mossaid"],
    ["Nizar", "Zouizra"],
    ["Nourddine", "Kissiri"]
]

# --- LISTE ÉLARGIE POUR ÉVITER LES DOUBLONS ---
prenoms = [
    "Amine", "Sarah", "Mohamed", "Salma", "Youssef", "Hajar", "Omar", "Kenza", "Mehdi", "Zineb",
    "Anas", "Rania", "Hamza", "Sofia", "Karim", "Noura", "Walid", "Rim", "Oussama", "Ghita",
    "Saad", "Aya", "Taha", "Manal", "Yassine", "Asmaa", "Bilal", "Kaoutar", "Nizar", "Meryem",
    "Ali", "Fatima", "Hassan", "Imane", "Driss", "Lamia", "Reda", "Sanae", "Ismail", "Houda"
]

noms = [
    "Berrada", "Fassi", "Benjelloun", "Chraibi", "El Amrani", "Daoudi", "Mernissi", "Tazi",
    "Bennani", "Idrissi", "Alami", "Mansouri", "Filali", "Naciri", "Guedira", "Benali",
    "Kabbaj", "Slaoui", "Ouazzani", "Tahiri", "Kadiri", "Zerrad", "Raiss", "Talbi", "Chaoui"
]

# --- VARIABLE GLOBALE POUR MÉMORISER TOUS LES LOGINS ---
# C'est ici que se joue la correction !
GLOBAL_LOGINS = set()

def generate_password():
    return ''.join(random.choices(string.ascii_lowercase + string.digits, k=8))

def generate_csv(filiere_name, nb_etudiants):
    filename = f"etudiants_{filiere_name.lower()}.csv"
    rows = []
    
    # 1. Insertion forcée des collègues (Cyber)
    if filiere_name == "Cybersecurite":
        for p, n in COLLEGUES:
            login = f"{p.lower()}.{n.lower()}"
            
            # Gestion doublon collègue (peu probable mais secure)
            original = login
            c = 1
            while login in GLOBAL_LOGINS:
                c += 1
                login = f"{original}{c}"
            
            GLOBAL_LOGINS.add(login)
            email = f"{login}@{DOMAIN_EMAIL}"
            rows.append([f"{p} {n}", email, login, generate_password()])

    # 2. Génération aléatoire
    attempts = 0
    while len(rows) < nb_etudiants:
        p = random.choice(prenoms)
        n = random.choice(noms)
        base_login = f"{p.lower()}.{n.lower()}"
        login = base_login
        
        # --- LOGIQUE INTELLIGENTE ---
        # Si amine.berrada existe déjà (même dans une autre filière), on fait amine.berrada2
        counter = 1
        while login in GLOBAL_LOGINS:
            counter += 1
            login = f"{base_login}{counter}"
        
        GLOBAL_LOGINS.add(login)
        
        email = f"{login}@{DOMAIN_EMAIL}"
        rows.append([f"{p} {n}", email, login, generate_password()])

    # Écriture
    with open(filename, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f, delimiter=';')
        writer.writerows(rows)
    
    print(f"✅ {filename} généré : {len(rows)} étudiants uniques (Total global: {len(GLOBAL_LOGINS)})")

# Exécution
print("--- Génération EIDIA (Logins Uniques Garantis) ---")
GLOBAL_LOGINS.clear() # On part de zéro
for filiere, count in CONFIG_FILIERES.items():
    generate_csv(filiere, count)
print("--- Terminé ! ---")