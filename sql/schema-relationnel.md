Utilisateur ("idUtil", nomUtil, prenomUtil, emailUtil, mdpUtil, roleUtil, nbTouites)
Image ("idImage", descImage, cheminImage)
Tag ("idTag", libelleTag, descTag)
Touite ("idTouite", texteTouite, datePub, nbEval, #idUtil, #idImage)
TouiteTag ("#idTouite", "#idTag")
Suivre ("#idSuiveur", "#idSuivi")
AbonnementTag ("#idUtil", "#idTag")


(Clés primaires entre guillemets "...", clés étrangères précédées de #)
