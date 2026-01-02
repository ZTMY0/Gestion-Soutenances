import csv
import random

# 1. Configuration
DOMAIN_EMAIL = "prof.ueuromed.org"
NB_PROFS = 25 
FILIERES = ["CYBER", "BIGDATA", "AI", "ROBO", "FULL"]

# --- LISTES ÉLARGIES (Pour plus de réalisme et moins de collisions) ---
prenoms_profs = [
    "Ahmed", "Driss", "Karima", "Layla", "Yassine", "Rachid", "Hassan", "Najat", "Siham", 
    "Fatim-Zahra", "Omar", "Khalid", "Mounia", "Samir", "Brahim", "Latifa", "Redouane"
]
noms_profs = [
    "El Amrani", "Bennani", "Zouhair", "Chraibi", "Alami", "Tazi", "Idrissi", "Mansouri", 
    "Kabbaj", "Filali", "Tahiri", "Berrada", "Daoudi", "Sefrioui", "Kadiri", "Benali"
]

def generate_profs_csv():
    filename = "profs_eidia.csv"
    rows = []
    
    # Set pour garantir l'unicité (comme pour les étudiants)
    generated_logins = set()
    
    for i in range(NB_PROFS):
        p = random.choice(prenoms_profs)
        n = random.choice(noms_profs)
        nom_complet = f"Pr. {p} {n}"
        email = f"{p.lower()}.{n.lower()}@{DOMAIN_EMAIL}"
        
        # LOGIQUE LOGIN : p.nom (ex: p.bennani)
        base_login = f"p.{n.lower()}"
        login = base_login
        
        # --- GESTION INTELLIGENTE DES DOUBLONS ---
        # Si p.bennani existe déjà, on crée p.bennani2, p.bennani3...
        counter = 1
        while login in generated_logins:
            counter += 1
            login = f"{base_login}{counter}"
        
        generated_logins.add(login)
        
        # Filière principale et spécialités secondaires
        filiere_principale = random.choice(FILIERES)
        
        # On évite que la spécialité soit la même que la filière principale (cosmétique)
        other_filieres = [f for f in FILIERES if f != filiere_principale]
        specs = random.sample(other_filieres, k=random.randint(1, 2))
        specialites_str = ",".join(specs)
        
        # FORMAT STRICT : Nom;Email;Login;Filière;Specs;MDP
        # Colonnes : 1, 2, 3, 4, 5, 6 -> Parfait pour ton PHP
        rows.append([nom_complet, email, login, filiere_principale, specialites_str, "Eidia2026"])

    # Écriture avec séparateur point-virgule (;)
    with open(filename, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f, delimiter=';')
        writer.writerows(rows)
    
    print(f"✅ {filename} généré avec {len(rows)} professeurs uniques.")

# Exécution
print("--- Génération du Dataset Professeurs ---")
generate_profs_csv()
print("--- Terminé ! ---")