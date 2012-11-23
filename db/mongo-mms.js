
/** item indexes **/
db.getCollection("item").ensureIndex({
  "_id": NumberInt(1)
},[
  
]);

/** user indexes **/
db.getCollection("user").ensureIndex({
  "_id": NumberInt(1)
},[
  
]);

/** item records **/
db.getCollection("item").insert({
  "_id": ObjectId("50af7b03f12a6f740a000002"),
  "alias": "mts",
  "title": "MTC Ukraine",
  "price": 1,
  "regex": "d{10,12}"
});
db.getCollection("item").insert({
  "_id": ObjectId("50af7b17f12a6f7c08000001"),
  "alias": "life",
  "title": "Life :)",
  "price": 1,
  "regex": "d{10,12}"
});

/** user records **/
db.getCollection("user").insert({
  "_id": ObjectId("50af7a71f12a6f740a000001"),
  "name": "Ivanov Ivan",
  "phone": "0071234567",
  "address": "Kiev, 24 Khreschatyk st."
});
db.getCollection("user").insert({
  "_id": ObjectId("50af7aa0f12a6f7c08000000"),
  "name": "Petrov Petr",
  "phone": "0099876543",
  "address": "Kiev, 2 Belarusskaya st."
});
