#import csv

class Contact:
    def __init__(self,Nom="",Prenom="",Telephone="",Email=""):
        self.Nom =Nom
        self.Prenom =Prenom
        self.Telephone =Telephone
        self.Email =Email

    def affiche_Info(self):
            print(f"Nom: {self.Nom}")
            print(f"Prénom: {self.Prenom}")
            print(f"Téléphone: {self.Telephone}")
            print(f"Email: {self.Email}")
        
def ajouter(liste_contacte):
        contact = Contact()        
        contact.Nom = input("Entre Nom :")
        contact.Prenom = input("Entre Prenom")
        contact.Telephone = input("Entre Telephone")
        contact.Email = input("Entre Email")
        liste_contacte.append(contact)
        liste_contacte.sort(key=lambda c: c.Nom.lower())
        #with open("contact.csv","a",newline="") as fichier:
        #    writer =csv.writer(fichier)
        #    writer.writerow([contact.Nom, contact.Prenom, contact.Telephone, contact.Email])
        print("\nContact ajouté :")
        
        contact.affiche_Info()
        
def Supprime(liste_contacte):
    nom = input("Entrez le Nom du contact à supprimer : ")
    
    for c in liste_contacte:
        if c.Nom.lower() == nom.lower():
            liste_contacte.remove(c)
            print(f"Contact '{nom}' supprimé !")
            return
    print("Contact non trouvé.")

def recherche(liste_contacte):
    nom = input("Entrez le Nom du contact à rechercher : ")
    for c in liste_contacte:
        if c.Nom.lower() == nom.lower():
            print("Contact trouvé :")
            c.affiche_Info()
            return
    print("Contact non trouvé.")


def affiche_Choix():
    liste_contacts = []
    while True:
        print("\n1- ajouter un contact ")
        print("\n2- Supprime un contact ")
        print("\n3- recherche un contact ")
        print("\n4- Quiter")
        
        choix = input("Entrez votre choix : ")
        
        if choix == "4":
            print("Au revoir")
            break
        elif choix == "1":
            ajouter(liste_contacts)
        elif choix == "2":
            Supprime(liste_contacts)
        elif choix == "3":
            recherche(liste_contacts)
        else:
            print("Choix invalide, réessayez.")
            
            
affiche_Choix()
            