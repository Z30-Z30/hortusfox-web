#!/bin/sh
# Fills the local demo instance with neutral sample data (fictional plants, generated illustrations).
# Usage: tidy/demo/seed.sh   (after: docker compose -f tidy/demo/docker-compose.yml up -d --build)
set -eu
cd "$(dirname "$0")"

APP=$(docker compose ps -q app)
DB=$(docker compose ps -q db)

docker cp img/. "$APP":/var/www/html/public/img/
docker exec "$APP" sh -c 'chown www-data:www-data /var/www/html/public/img/demo-*'

docker exec -i "$DB" mariadb -uhortusfox -pdemodb123 hortusfox <<'SQL'
SET NAMES utf8mb4;
DELETE FROM PlantTasksRefModel; DELETE FROM TasksModel; DELETE FROM CalendarModel; DELETE FROM PlantsModel;
DELETE FROM LocationsModel; DELETE FROM InventoryModel; DELETE FROM InvGroupModel; DELETE FROM LogModel;
ALTER TABLE PlantsModel AUTO_INCREMENT = 1; ALTER TABLE LocationsModel AUTO_INCREMENT = 1; ALTER TABLE TasksModel AUTO_INCREMENT = 1;

UPDATE AppModel SET workspace = 'Green Corner', chat_enable = 0, scroller = 0, date_format = 'M j' WHERE id = 1;
UPDATE UserModel SET name = 'Alex', show_log = 0, show_calendar_view = 0 WHERE id = 1;

INSERT INTO LocationsModel (id, name, active) VALUES (1,'Living room',1),(2,'Kitchen',1),(3,'Office',1),(4,'Balcony',1),(5,'Bedroom',1),(6,'Bathroom',1);

INSERT INTO PlantsModel (id, name, scientific_name, location, tags, photo, last_watered, last_fertilised, last_repotted, lifespan, hardy, humidity, light_level, health_state, notes, last_edited_user, last_edited_date) VALUES
(1,'Monstera','Monstera deliciosa',1,'indoor foliage','demo-monstera_thumb.jpg',NOW() - INTERVAL 6 DAY,NOW() - INTERVAL 40 DAY,NOW() - INTERVAL 300 DAY,'lifespan_perennial',0,60,'light_level_indirect_light','in_good_standing','Likes a moss pole. Wipe the leaves once a month.',1,NOW() - INTERVAL 1 DAY),
(2,'Rubber plant','Ficus elastica',1,'indoor foliage','demo-rubber-plant_thumb.jpg',NOW() - INTERVAL 9 DAY,NOW() - INTERVAL 40 DAY,NULL,'lifespan_perennial',0,50,'light_level_indirect_light','in_good_standing',NULL,1,NOW() - INTERVAL 3 DAY),
(3,'Peace lily','Spathiphyllum wallisii',1,'indoor flowering','demo-peace-lily_thumb.jpg',NOW() - INTERVAL 4 DAY,NOW() - INTERVAL 25 DAY,NULL,'lifespan_perennial',0,65,'light_level_half_shade','in_good_standing',NULL,1,NOW() - INTERVAL 2 DAY),
(4,'Calathea','Goeppertia orbifolia',1,'indoor foliage','demo-calathea_thumb.jpg',NOW() - INTERVAL 2 DAY,NOW() - INTERVAL 60 DAY,NULL,'lifespan_perennial',0,70,'light_level_filtered_light','overwatered','Leaf edges curled after the last watering. Let the soil dry out.',1,NOW() - INTERVAL 2 HOUR),
(5,'Bird of paradise','Strelitzia nicolai',1,'indoor','demo-strelitzia_thumb.jpg',NOW() - INTERVAL 6 DAY,NOW() - INTERVAL 40 DAY,NULL,'lifespan_perennial',0,55,'light_level_sunny','in_good_standing',NULL,1,NOW() - INTERVAL 5 DAY),
(6,'Boston fern','Nephrolepis exaltata',1,'indoor foliage','demo-fern_thumb.jpg',NOW() - INTERVAL 3 DAY,NOW() - INTERVAL 30 DAY,NULL,'lifespan_perennial',0,75,'light_level_half_shade','in_good_standing',NULL,1,NOW() - INTERVAL 6 DAY),
(7,'Basil','Ocimum basilicum',2,'herb edible','demo-basil_thumb.jpg',NOW() - INTERVAL 1 DAY,NOW() - INTERVAL 14 DAY,NULL,'lifespan_annual',0,50,'light_level_sunny','in_good_standing',NULL,1,NOW() - INTERVAL 1 DAY),
(8,'Rosemary','Salvia rosmarinus',2,'herb edible','demo-rosemary_thumb.jpg',NOW() - INTERVAL 5 DAY,NOW() - INTERVAL 50 DAY,NULL,'lifespan_perennial',1,40,'light_level_sunny','in_good_standing',NULL,1,NOW() - INTERVAL 7 DAY),
(9,'Aloe vera','Aloe vera',2,'succulent','demo-aloe_thumb.jpg',NOW() - INTERVAL 16 DAY,NOW() - INTERVAL 90 DAY,NULL,'lifespan_perennial',0,35,'light_level_sunny','in_good_standing',NULL,1,NOW() - INTERVAL 8 DAY),
(10,'Snake plant','Dracaena trifasciata',3,'indoor succulent','demo-snake-plant_thumb.jpg',NOW() - INTERVAL 18 DAY,NOW() - INTERVAL 80 DAY,NULL,'lifespan_perennial',0,40,'light_level_half_shade','in_good_standing',NULL,1,NOW() - INTERVAL 9 DAY),
(11,'Pothos','Epipremnum aureum',3,'indoor hanging','demo-pothos_thumb.jpg',NOW() - INTERVAL 7 DAY,NOW() - INTERVAL 45 DAY,NULL,'lifespan_perennial',0,55,'light_level_indirect_light','in_good_standing',NULL,1,NOW() - INTERVAL 4 DAY),
(12,'Cherry tomato','Solanum lycopersicum',4,'edible balcony','demo-tomato_thumb.jpg',NOW() - INTERVAL 1 DAY,NOW() - INTERVAL 10 DAY,NULL,'lifespan_annual',0,50,'light_level_sunny','in_good_standing',NULL,1,NOW() - INTERVAL 10 DAY);

INSERT INTO TasksModel (id, title, description, due_date, recurring_time, recurring_scope, done) VALUES
(1,'Water: Monstera','',CURDATE() + INTERVAL 1 DAY,168,'days',0),
(2,'Water: Peace lily','',CURDATE() + INTERVAL 1 DAY,168,'days',0),
(3,'Water: Boston fern','',CURDATE() + INTERVAL 1 DAY,168,'days',0),
(4,'Water: Bird of paradise','',CURDATE() + INTERVAL 1 DAY,168,'days',0),
(5,'Water: Basil','Keep the soil evenly moist.',CURDATE(),48,'days',0),
(6,'Water: Cherry tomato','',CURDATE(),48,'days',0),
(7,'Water: Rubber plant','',CURDATE() + INTERVAL 4 DAY,336,'days',0),
(8,'Water: Pothos','',CURDATE() + INTERVAL 4 DAY,240,'days',0),
(9,'Check Calathea soil','Only water again when the top 3 cm are dry.',CURDATE() - INTERVAL 1 DAY,NULL,'hours',0),
(10,'Fertilise: herbs','Half-strength liquid fertiliser.',CURDATE() + INTERVAL 6 DAY,336,'days',0),
(11,'Water: Aloe vera','',CURDATE() + INTERVAL 9 DAY,504,'days',0),
(12,'Water: Snake plant','',CURDATE() + INTERVAL 9 DAY,504,'days',0),
(13,'Wipe leaves','All foliage plants in the living room.',CURDATE() + INTERVAL 14 DAY,720,'days',0),
(14,'Repot: Monstera','Next pot size up, fresh aroid mix.',CURDATE() + INTERVAL 45 DAY,8760,'years',0),
(15,'Prune: Rosemary','',CURDATE() + INTERVAL 80 DAY,8760,'years',0),
(16,'Buy potting soil','',NULL,NULL,'hours',0),
(17,'Water: Calathea','',CURDATE() - INTERVAL 2 DAY,NULL,'hours',1);

INSERT INTO PlantTasksRefModel (plant_id, task_id) VALUES (1,1),(3,2),(6,3),(5,4),(7,5),(12,6),(2,7),(11,8),(4,9),(9,11),(10,12),(1,14),(8,15),(4,17);

INSERT INTO CalendarModel (name, date_from, date_till, class_name, color_background, color_border, last_edited_user, last_edited_date) VALUES
('Water living room', CURDATE() + INTERVAL 1 DAY, CURDATE() + INTERVAL 2 DAY, 'water', 'rgb(76, 135, 195)', 'rgb(131, 183, 251)', 1, NOW()),
('Water herbs', CURDATE(), CURDATE() + INTERVAL 1 DAY, 'water', 'rgb(76, 135, 195)', 'rgb(131, 183, 251)', 1, NOW()),
('Fertilise herbs', CURDATE() + INTERVAL 6 DAY, CURDATE() + INTERVAL 7 DAY, 'fertilise', 'rgb(135, 195, 102)', 'rgb(205, 240, 167)', 1, NOW()),
('Water living room', CURDATE() + INTERVAL 8 DAY, CURDATE() + INTERVAL 9 DAY, 'water', 'rgb(76, 135, 195)', 'rgb(131, 183, 251)', 1, NOW()),
('Away: ask a neighbour to water', CURDATE() + INTERVAL 11 DAY, CURDATE() + INTERVAL 14 DAY, 'other', 'rgb(150, 150, 150)', 'rgb(200, 200, 200)', 1, NOW()),
('Wipe leaves', CURDATE() + INTERVAL 14 DAY, CURDATE() + INTERVAL 15 DAY, 'treat', 'rgb(200, 111, 111)', 'rgb(255, 150, 150)', 1, NOW()),
('Harvest basil', CURDATE() + INTERVAL 3 DAY, CURDATE() + INTERVAL 4 DAY, 'harvest', 'rgb(72, 243, 65)', 'rgb(180, 250, 155)', 1, NOW()),
('Water living room', CURDATE() - INTERVAL 6 DAY, CURDATE() - INTERVAL 5 DAY, 'water', 'rgb(76, 135, 195)', 'rgb(131, 183, 251)', 1, NOW());

INSERT INTO InvGroupModel (token, label) VALUES ('fertiliser','Fertiliser'),('soil','Soil'),('pots','Pots'),('tools','Tools');
INSERT INTO InventoryModel (name, group_ident, description, tags, location, amount, last_edited_user, last_edited_date) VALUES
('Liquid fertiliser 500 ml','fertiliser','Universal, dilute 1:100.','liquid','Kitchen cupboard',2,1,NOW()),
('Aroid mix 10 l','soil','Chunky mix for Monstera and Pothos.','indoor','Cellar',1,1,NOW()),
('Herb soil 5 l','soil',NULL,'herbs','Cellar',0,1,NOW()),
('Terracotta pot 14 cm','pots',NULL,'terracotta','Balcony box',4,1,NOW()),
('Pruning shears','tools',NULL,NULL,'Kitchen drawer',1,1,NOW());
SQL
echo "Demo data loaded. Sign in at http://127.0.0.1:8090 with the demo account from docker-compose.yml."
