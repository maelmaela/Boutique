<?php
//Fonctions acces aux données

function SelectClients(): array
{
    $Clients =
        [
            [
                "nom" => "Toto",
                "prenom" => "tta",
                "telephone" => "774512437",
                "adresse" => "medina",
                "dette" => [
                    "identifiant" => "774512437",
                    "etat" => "1",
                    "date" => date("d/m/Y"),
                    "mntdette" => "400.000",
                    "montant" => [],
                ],
                "payement" => [
                    "ref" => "P1",
                    "montantV" => 200000,
                    "date" => date("d/m/Y"),
                ],
            ],
            [
                "nom" => "Wane",
                "prenom" => "Baila",
                "telephone" => "784512437",
                "adresse" => "FO",
                "dette" => [
                    "identifiant" => "784512437",
                    "etat" => "0",
                    "date" => date("d/m/Y"),
                    "mntdette" => "400.000",
                    "montant" => [],
                ],
                "payement" => [
                    "ref" => "P2",
                    "montantV" => 200000,
                    "date" => date("d/m/Y"),
                ],
            ],
        ];
    // foreach ($Clients as $key => $value) {
    //     //key => 0;1
    //     //value => client
    //     echo"Nom: ".$value["nom"];
    //     echo"Prenom: ".$value["prenom"];
    //     echo"Telephone: ".$value["telephone"];
    //     echo"Adresse: ".$value["adresse"];
    // };
    return $Clients;
    // $Clients[1]["telephone"]
    //tab vilet tab numerique
};

function SelectClientsByTel(array $Clients, string $tel): array|null
{
    foreach ($Clients as $client) {
        if ($client["telephone"] == $tel) {
            return $client;
        };
    };
    return null;
};

function InsertClient(array &$Clients, $client): void
{
    $Clients[] = $client;
};
// array|null Union de type

//Fonctions Services ou usecase ou métier


function ListerClient(array $Clients)
{
    return SelectClients();
};

function estVide(string $value): bool
{
    return empty($value);
}

function RecherchebyTel($tel, $Clients)
{
    if (SelectClientsByTel($Clients, $tel)) {
        foreach ($Clients as  $client) {
            if ($client["telephone"] == $tel) {
                echo "\n**********************************\n";
                echo "Téléphone : " . $client["telephone"] . "\t";
                echo "Nom : " . $client["nom"] . "\t";
                echo "Prénom : " . $client["prenom"] . "\t";
                echo "Adresse : " . $client["adresse"] . "\t";
                echo "Identifiant : " . $client["dette"]["identifiant"] . "\t";
                echo "Date : " . $client["dette"]["date"] . "\t";
                echo "Montant : " . $client["dette"]["mntdette"] . "\t";
            }
        }
    } else {
        echo "Le client n'existe pas";
    }
}

function etat(array $Clients): bool
{
    foreach ($Clients as $client) {
        if ($client["dette"]["etat"] == 1) {
            return false;
        }
    }
    return true;
}

//Fonctions de présentation
//on a que des echo
//console html/css

function saisiChampObligatoire(string $sms): string
{
    do {
        $value = readline($sms);
    } while (estVide($value));
    return $value;
}

function TelIsUnique(array $Clients, string $sms): string
{
    do {
        $value = readline($sms);
    } while (estVide($value) || SelectClientsByTel($Clients, $value) != null);
    return $value;
};


function saisieClient(array $Clients): array
{
    return [
        "telephone" => TelIsUnique($Clients, "Entrez le numero de Téléphone: "),
        "nom" => saisiChampObligatoire("Entrez le nom: "),
        "prenom" => saisiChampObligatoire("Entrez le prénom: "),
        "adresse" => saisiChampObligatoire("Entrez l'adresse': "),
    ];
};

function EnregistrerClient(array &$Clients, array $client): bool
{
    $result = SelectClientsByTel($Clients, $client["telephone"]);
    if ($result == null) {
        InsertClient($Clients, $client);
        return true;
    };
    return false;
};

function ValidEtat(array $Clients): bool
{
    return etat($Clients);
}

function saisieDettes(array $Clients): array
{
    $identifiant = saisiChampObligatoire("Entrez le numéro de téléphone du client : ");
    $client = SelectClientsByTel($Clients, $identifiant);
    if ($client === null) {
        echo "Le client avec ce numéro de téléphone n'existe pas.\n";
        return [];
    }
    if ($client["dette"]["etat"] === "1") {
        echo "Ce client a déjà une dette en cours.\n";
        return [];
    }
    return [
        "etat" => "1",
        "identifiant" => $identifiant,
        "date" => date("d/m/Y"),
        "mntdette" => saisiChampObligatoire("Entrez le montant que vous voulez emprunter : "),
    ];
}


function EnregistrerDette(array &$Clients, array $dette): bool
{
    foreach ($Clients as &$client) {
        if ($client["telephone"] == $dette["identifiant"] && $client["dette"]["etat"] == "0") {
            $client["dette"] = $dette;
            return true;
        }
    }
    return false;
}

function saisiePaiement(): array
{
    $identifiant = readline("Entrez le numéro de téléphone du client : ");
    $montant = (int)readline("Entrez le montant payé : ");
    return [
        "identifiant" => $identifiant,
        "montant" => $montant,
        "date" => date("d/m/Y"),
    ];
}

function EnregistrerPaiement(array &$Clients, array $paiement): bool
{
    foreach ($Clients as &$client) {
        if ($client["telephone"] === $paiement["identifiant"]) {
            if ($client["dette"]["etat"] === "1") {
                $nouveauMontant = (int)str_replace(".", "", $client["dette"]["mntdette"]) - $paiement["montant"];
                if ($nouveauMontant <= 0) {
                    $client["dette"] = [
                        "etat" => "0",
                        "identifiant" => $client["telephone"],
                        "date" => date("d/m/Y"),
                        "mntdette" => "0",
                    ];
                    echo "La dette a été totalement remboursée.\n";
                } else {
                    $client["dette"]["mntdette"] = number_format($nouveauMontant, 0, ".", ".");
                    echo "Paiement enregistré. Dette restante : " . $client["dette"]["mntdette"] . " FCFA\n";
                }
                return true;
            } else {
                echo "Ce client n'a pas de dette en cours.\n";
                return false;
            }
        }
    }
    echo "Client introuvable.\n";
    return false;
}


function InsertDette(array &$Clients, $dette): void
{
    $Clients[] = $dette;
};

function Affiche_Clients(array $Clients): void
{
    if (count($Clients) == 0) {
        echo "Il n'ya pas de clients à afficher";
    } else {
        foreach ($Clients as  $client) {
            echo "\n**********************************\n";
            echo "Téléphone : " . $client["telephone"] . "\t";
            echo "Nom : " . $client["nom"] . "\t";
            echo "Prénom : " . $client["prenom"] . "\t";
            echo "Adresse : " . $client["adresse"] . "\t";
        }
    }
}

function Afficher_Dettes(array $Clients): void
{
    if (count($Clients) == 0) {
        echo "Il n'ya pas de dette à afficher";
    } else {
        foreach ($Clients as  $client) {
            echo "\n**********************************\n";
            echo "Nom du client : " . $client["nom"] . "\t";
            echo "Prénom du client: " . $client["prenom"] . "\t";
            echo "Identifiant : " . $client["dette"]["identifiant"] . "\t";
            echo "Date : " . $client["dette"]["date"] . "\t";
            echo "Montant" . $client["dette"]["mntdette"] . "\t";
        }
    }
}

function Afficher_Paiement(array $Clients): void
{
    if (count($Clients) == 0) {
        echo "Il n'ya pas de dette à afficher";
    } else {
        foreach ($Clients as  $client) {
            echo "\n**********************************\n";
            echo "Nom du client : " . $client["nom"] . "\t";
            echo "Prénom du client: " . $client["prenom"] . "\t";
            echo "Reference du paiement : " . $client["payement"]["ref"] . "\t";
            echo "Montant versé : " . $client["payement"]["montantV"] . "\t";
            echo "Date : " . $client["payement"]["date"] . "\t";
        }
    }
}


function Menu_Client(): int
{
    echo "1 - Ajouter un client\n";
    echo "2 - Lister les clients\n";
    echo "3 - Rechercher un client par téléphone\n";
    echo "4 - Quitter\n";
    return (int)readline("Faite votre choix: ");
};

function Menu_Dette(): int
{
    echo "1 - Ajouter une dette\n";
    echo "2 - Lister les dettes\n";
    echo "3 - Rechercher une dette par son identifiant\n";
    echo "4 - Enregistrer un paiement\n";
    echo "5 - Lister les paiements\n";
    echo "6 - Quitter\n";
    return (int)readline("Faite votre choix: ");
}


function Menu_Principal(): int
{
    echo "1 - Clients\n";
    echo "2 - Dettes\n";
    echo "3 - Quitter\n";
    return (int)readline("Faite votre choix: ");
}

function Principal_Client()
{
    $Clients = SelectClients();
    do {
        $choix = Menu_Client();
        switch ($choix) {
            case 1:
                $client = saisieClient($Clients);
                if (EnregistrerClient($Clients, $client)) {
                    echo "Clients enregistré avec succes \n";
                } else {
                    echo "Le numero de téléphone existe déja \n";
                }
                break;
            case 2:
                Affiche_Clients($Clients);
                break;
            case 3:
                $tel = (int)readline("Entrez le numero de téléphone: ");
                RecherchebyTel($tel, $Clients);
                break;
            case 4:
                break;
            default:
                # code...
                break;
        }
    } while ($choix != 4);
}

function Principal_Dette()
{
    $Clients = SelectClients();
    do {
        $choix = Menu_Dette();
        switch ($choix) {
            case 1:
                $dette = saisieDettes($Clients);
                if (!empty($dette) && EnregistrerDette($Clients, $dette)) {
                    echo "Dette enregistrée avec succès.\n";
                } else {
                    echo "Impossible d'enregistrer la dette.\n";
                }
                break;
            case 2:
                Afficher_Dettes($Clients);
                break;
            case 3:
                $tel = saisiChampObligatoire("Entrez le numéro de téléphone : ");
                RecherchebyTel($tel, $Clients);
                break;
            case 4:
                $paiement = saisiePaiement();
                EnregistrerPaiement($Clients, $paiement);
                break;
            case 5:
                Afficher_Paiement($Clients);
                break;
            case 6:
                echo "Fin du programme.\n";
                break;
            default:
                echo "Choix invalide.\n";
        }
    } while ($choix != 5);
}


function Gerer_menu_principal()
{
    do {
        $choix = Menu_Principal();
        switch ($choix) {
            case 1:
                Principal_Client();
                break;
            case 2:
                Principal_Dette();
                break;
            case 3:
                echo "Fin du programme.\n";
                break;
            default:
                echo "Choix invalide.\n";
        }
    } while ($choix != 4);
}


Gerer_menu_principal();
