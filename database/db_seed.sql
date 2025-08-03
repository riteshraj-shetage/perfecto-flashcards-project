-- Seed data for perfecto language learning app

-- Insert users (admin and test user)
INSERT INTO users (username, email, password, role) VALUES
('Admin', 'admin@perfecto.com', 'admin123', 'admin'),
('User', 'user@perfecto.com', 'user123', 'user');

-- Insert languages
INSERT INTO languages (name, code, flag_url, phrase_count, display_order) VALUES
('Spanish', 'es', 'https://placehold.co/150x150/FF9933/FFFFFF?text=ES', 100, 1),
('French', 'fr', 'https://placehold.co/150x150/0055A4/FFFFFF?text=FR', 85, 2),
('German', 'de', 'https://placehold.co/150x150/000000/FFCC00?text=DE', 70, 3);

-- Insert categories
-- Spanish categories
INSERT INTO categories (language_id, name, slug, description, display_order) VALUES
(1, 'Greetings', 'greetings', 'Basic greetings and introductions in Spanish', 1),
(1, 'Food & Dining', 'food', 'Essential vocabulary for ordering food and dining out', 2),
(1, 'Travel', 'travel', 'Useful phrases for traveling in Spanish-speaking countries', 3),
(1, 'Colors', 'colors', 'Common colors in Spanish', 4),
(1, 'Days of the Week', 'days', 'Days of the week in Spanish', 5);

-- French categories
INSERT INTO categories (language_id, name, slug, description, display_order) VALUES
(2, 'Greetings', 'greetings', 'Basic greetings and introductions in French', 1),
(2, 'Food & Dining', 'food', 'Essential vocabulary for ordering food and dining out', 2),
(2, 'Travel', 'travel', 'Useful phrases for traveling in French-speaking countries', 3),
(2, 'Colors', 'colors', 'Common colors in French', 4),
(2, 'Days of the Week', 'days', 'Days of the week in French', 5);

-- German categories
INSERT INTO categories (language_id, name, slug, description, display_order) VALUES
(3, 'Greetings', 'greetings', 'Basic greetings and introductions in German', 1),
(3, 'Food & Dining', 'food', 'Essential vocabulary for ordering food and dining out', 2),
(3, 'Travel', 'travel', 'Useful phrases for traveling in German-speaking countries', 3),
(3, 'Colors', 'colors', 'Common colors in German', 4),
(3, 'Days of the Week', 'days', 'Days of the week in German', 5);

-- Insert flashcards
-- Spanish Greetings
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(1, 'Hello', 'Hola', 'OH-lah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Hola', 1),
(1, 'Good morning', 'Buenos días', 'BWEH-nohs DEE-ahs', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Buenos+días', 2),
(1, 'Good afternoon', 'Buenas tardes', 'BWEH-nahs TAR-dehs', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Buenas+tardes', 3),
(1, 'Good evening', 'Buenas noches', 'BWEH-nahs NOH-chehs', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Buenas+noches', 4),
(1, 'How are you?', '¿Cómo estás?', 'KOH-moh eh-STAHS', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Cómo+estás', 5);

-- Spanish Food & Dining flashcards (category_id = 2)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(2, 'Water', 'Agua', 'AH-gwah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Agua', 1),
(2, 'Bread', 'Pan', 'PAHN', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Pan', 2),
(2, 'Coffee', 'Café', 'kah-FEH', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Café', 3),
(2, 'Tea', 'Té', 'TEH', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Té', 4),
(2, 'Menu', 'Menú', 'meh-NOO', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Menú', 5),
(2, 'Waiter', 'Camarero', 'kah-mah-REH-roh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Camarero', 6),
(2, 'Bill', 'Cuenta', 'KWEHN-tah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Cuenta', 7),
(2, 'Table', 'Mesa', 'MEH-sah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Mesa', 8),
(2, 'Reservation', 'Reserva', 'reh-SEHR-bah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Reserva', 9),
(2, 'Delicious', 'Delicioso', 'deh-lee-SYOH-soh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Delicioso', 10);

-- French Greetings
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(4, 'Hello', 'Bonjour', 'bohn-ZHOOR', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Bonjour', 1),
(4, 'Good evening', 'Bonsoir', 'bohn-SWAHR', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Bonsoir', 2),
(4, 'How are you?', 'Comment allez-vous?', 'koh-mahn tah-lay-VOO', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Comment+allez-vous', 3),
(4, 'My name is...', 'Je m\'appelle...', 'zhuh mah-PEHL', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Je+m\'appelle', 4),
(4, 'Nice to meet you', 'Enchanté', 'ahn-shahn-TAY', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Enchanté', 5);

-- French Food & Dining flashcards (category_id = 5)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(5, 'Water', 'Eau', 'oh', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Eau', 1),
(5, 'Bread', 'Pain', 'pan', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Pain', 2),
(5, 'Coffee', 'Café', 'kah-FAY', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Café', 3),
(5, 'Tea', 'Thé', 'tay', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Thé', 4),
(5, 'Menu', 'Menu', 'muh-NEW', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Menu', 5),
(5, 'Waiter', 'Serveur', 'sehr-VUHR', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Serveur', 6),
(5, 'Bill', 'Addition', 'ah-dee-SYON', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Addition', 7),
(5, 'Table', 'Table', 'tabl', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Table', 8),
(5, 'Reservation', 'Réservation', 'ray-zehr-va-SYON', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Réservation', 9),
(5, 'Delicious', 'Délicieux', 'day-lee-SYEU', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Délicieux', 10);

-- German Greetings
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(7, 'Hello', 'Hallo', 'HAH-loh', 'https://placehold.co/300x200/000000/FFCC00?text=Hallo', 1),
(7, 'Good morning', 'Guten Morgen', 'GOO-ten MOR-gen', 'https://placehold.co/300x200/000000/FFCC00?text=Guten+Morgen', 2),
(7, 'Good afternoon', 'Guten Tag', 'GOO-ten tahk', 'https://placehold.co/300x200/000000/FFCC00?text=Guten+Tag', 3),
(7, 'Good evening', 'Guten Abend', 'GOO-ten AH-bent', 'https://placehold.co/300x200/000000/FFCC00?text=Guten+Abend', 4),
(7, 'How are you?', 'Wie geht es dir?', 'vee GAYT es deer', 'https://placehold.co/300x200/000000/FFCC00?text=Wie+geht+es+dir', 5);

-- German Food & Dining flashcards (category_id = 8)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(8, 'Water', 'Wasser', 'VAH-ser', 'https://placehold.co/300x200/000000/FFCC00?text=Wasser', 1),
(8, 'Bread', 'Brot', 'broht', 'https://placehold.co/300x200/000000/FFCC00?text=Brot', 2),
(8, 'Coffee', 'Kaffee', 'KAH-fay', 'https://placehold.co/300x200/000000/FFCC00?text=Kaffee', 3),
(8, 'Tea', 'Tee', 'tay', 'https://placehold.co/300x200/000000/FFCC00?text=Tee', 4),
(8, 'Menu', 'Speisekarte', 'SHPI-zeh-kar-teh', 'https://placehold.co/300x200/000000/FFCC00?text=Speisekarte', 5),
(8, 'Waiter', 'Kellner', 'KELL-ner', 'https://placehold.co/300x200/000000/FFCC00?text=Kellner', 6),
(8, 'Bill', 'Rechnung', 'REKH-noong', 'https://placehold.co/300x200/000000/FFCC00?text=Rechnung', 7),
(8, 'Table', 'Tisch', 'tish', 'https://placehold.co/300x200/000000/FFCC00?text=Tisch', 8),
(8, 'Reservation', 'Reservierung', 'reh-zehr-VEE-roong', 'https://placehold.co/300x200/000000/FFCC00?text=Reservierung', 9),
(8, 'Delicious', 'Lecker', 'LEK-er', 'https://placehold.co/300x200/000000/FFCC00?text=Lecker', 10);

-- Spanish Travel flashcards (category_id = 3)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(3, 'Airport', 'Aeropuerto', 'ah-eh-roh-PWEHR-toh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Aeropuerto', 1),
(3, 'Train', 'Tren', 'tren', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Tren', 2),
(3, 'Bus', 'Autobús', 'ow-toh-BOOS', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Autobús', 3),
(3, 'Ticket', 'Boleto', 'boh-LEH-toh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Boleto', 4),
(3, 'Hotel', 'Hotel', 'oh-TEL', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Hotel', 5),
(3, 'Taxi', 'Taxi', 'TAHK-see', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Taxi', 6),
(3, 'Map', 'Mapa', 'MAH-pah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Mapa', 7),
(3, 'Passport', 'Pasaporte', 'pah-sah-POHR-teh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Pasaporte', 8),
(3, 'Luggage', 'Equipaje', 'eh-kee-PAH-heh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Equipaje', 9),
(3, 'Where is...?', '¿Dónde está...?', 'DON-deh es-TAH', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Donde+esta', 10);

-- French Travel flashcards (category_id = 6)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(6, 'Airport', 'Aéroport', 'ah-eh-roh-PORT', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Aéroport', 1),
(6, 'Train', 'Train', 'trahn', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Train', 2),
(6, 'Bus', 'Bus', 'bus', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Bus', 3),
(6, 'Ticket', 'Billet', 'bee-YAY', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Billet', 4),
(6, 'Hotel', 'Hôtel', 'oh-TEL', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Hôtel', 5),
(6, 'Taxi', 'Taxi', 'tahk-SEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Taxi', 6),
(6, 'Map', 'Carte', 'kart', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Carte', 7),
(6, 'Passport', 'Passeport', 'pass-PORT', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Passeport', 8),
(6, 'Luggage', 'Bagages', 'bah-GAHZH', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Bagages', 9),
(6, 'Where is...?', 'Où est...?', 'oo eh', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Ou+est', 10);

-- German Travel flashcards (category_id = 9)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(9, 'Airport', 'Flughafen', 'FLOOK-hah-fen', 'https://placehold.co/300x200/000000/FFCC00?text=Flughafen', 1),
(9, 'Train', 'Zug', 'tsook', 'https://placehold.co/300x200/000000/FFCC00?text=Zug', 2),
(9, 'Bus', 'Bus', 'boos', 'https://placehold.co/300x200/000000/FFCC00?text=Bus', 3),
(9, 'Ticket', 'Fahrkarte', 'FAHR-kar-teh', 'https://placehold.co/300x200/000000/FFCC00?text=Fahrkarte', 4),
(9, 'Hotel', 'Hotel', 'ho-TEL', 'https://placehold.co/300x200/000000/FFCC00?text=Hotel', 5),
(9, 'Taxi', 'Taxi', 'TAHK-see', 'https://placehold.co/300x200/000000/FFCC00?text=Taxi', 6),
(9, 'Map', 'Karte', 'KAR-teh', 'https://placehold.co/300x200/000000/FFCC00?text=Karte', 7),
(9, 'Passport', 'Reisepass', 'RYE-zeh-pass', 'https://placehold.co/300x200/000000/FFCC00?text=Reisepass', 8),
(9, 'Luggage', 'Gepäck', 'geh-PECK', 'https://placehold.co/300x200/000000/FFCC00?text=Gepäck', 9),
(9, 'Where is...?', 'Wo ist...?', 'voh ist', 'https://placehold.co/300x200/000000/FFCC00?text=Wo+ist', 10);

-- Spanish Colors flashcards (category_id = 14)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(14, 'Red', 'Rojo', 'ROH-ho', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Rojo', 1),
(14, 'Blue', 'Azul', 'ah-SOOL', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Azul', 2),
(14, 'Green', 'Verde', 'BEHR-deh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Verde', 3),
(14, 'Yellow', 'Amarillo', 'ah-mah-REE-yoh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Amarillo', 4),
(14, 'Black', 'Negro', 'NEH-groh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Negro', 5),
(14, 'White', 'Blanco', 'BLAN-koh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Blanco', 6),
(14, 'Orange', 'Naranja', 'nah-RAHN-hah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Naranja', 7),
(14, 'Pink', 'Rosa', 'ROH-sah', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Rosa', 8),
(14, 'Purple', 'Morado', 'moh-RAH-doh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Morado', 9),
(14, 'Brown', 'Marrón', 'mah-ROHN', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Marrón', 10);

-- French Colors flashcards (category_id = 15)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(15, 'Red', 'Rouge', 'roozh', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Rouge', 1),
(15, 'Blue', 'Bleu', 'bluh', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Bleu', 2),
(15, 'Green', 'Vert', 'vehr', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Vert', 3),
(15, 'Yellow', 'Jaune', 'zhawn', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Jaune', 4),
(15, 'Black', 'Noir', 'nwar', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Noir', 5),
(15, 'White', 'Blanc', 'blahn', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Blanc', 6),
(15, 'Orange', 'Orange', 'oh-RAHNZH', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Orange', 7),
(15, 'Pink', 'Rose', 'rohz', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Rose', 8),
(15, 'Purple', 'Violet', 'vee-oh-LAY', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Violet', 9),
(15, 'Brown', 'Marron', 'mah-ROHN', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Marron', 10);

-- German Colors flashcards (category_id = 16)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(16, 'Red', 'Rot', 'roht', 'https://placehold.co/300x200/000000/FFCC00?text=Rot', 1),
(16, 'Blue', 'Blau', 'blau', 'https://placehold.co/300x200/000000/FFCC00?text=Blau', 2),
(16, 'Green', 'Grün', 'gruen', 'https://placehold.co/300x200/000000/FFCC00?text=Grün', 3),
(16, 'Yellow', 'Gelb', 'gelp', 'https://placehold.co/300x200/000000/FFCC00?text=Gelb', 4),
(16, 'Black', 'Schwarz', 'shvahrts', 'https://placehold.co/300x200/000000/FFCC00?text=Schwarz', 5),
(16, 'White', 'Weiß', 'vice', 'https://placehold.co/300x200/000000/FFCC00?text=Weiß', 6),
(16, 'Orange', 'Orange', 'oh-RAHN-ge', 'https://placehold.co/300x200/000000/FFCC00?text=Orange', 7),
(16, 'Pink', 'Rosa', 'ROH-zah', 'https://placehold.co/300x200/000000/FFCC00?text=Rosa', 8),
(16, 'Purple', 'Lila', 'LEE-lah', 'https://placehold.co/300x200/000000/FFCC00?text=Lila', 9),
(16, 'Brown', 'Braun', 'brown', 'https://placehold.co/300x200/000000/FFCC00?text=Braun', 10);

-- Spanish Days of the Week (category_id = 17)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(17, 'Monday', 'Lunes', 'LOO-ness', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Lunes', 1),
(17, 'Tuesday', 'Martes', 'MAR-tess', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Martes', 2),
(17, 'Wednesday', 'Miércoles', 'MYER-coh-less', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Miércoles', 3),
(17, 'Thursday', 'Jueves', 'HWEH-vess', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Jueves', 4),
(17, 'Friday', 'Viernes', 'VYER-ness', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Viernes', 5),
(17, 'Saturday', 'Sábado', 'SAH-bah-doh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Sábado', 6),
(17, 'Sunday', 'Domingo', 'doh-MEEN-goh', 'https://placehold.co/300x200/FF9933/FFFFFF?text=Domingo', 7);

-- French Days of the Week (category_id = 18)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(18, 'Monday', 'Lundi', 'lun-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Lundi', 1),
(18, 'Tuesday', 'Mardi', 'mar-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Mardi', 2),
(18, 'Wednesday', 'Mercredi', 'mehr-cruh-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Mercredi', 3),
(18, 'Thursday', 'Jeudi', 'zhuh-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Jeudi', 4),
(18, 'Friday', 'Vendredi', 'von-druh-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Vendredi', 5),
(18, 'Saturday', 'Samedi', 'sam-DEE', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Samedi', 6),
(18, 'Sunday', 'Dimanche', 'dee-MONSH', 'https://placehold.co/300x200/0055A4/FFFFFF?text=Dimanche', 7);

-- German Days of the Week (category_id = 19)
INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, display_order) VALUES
(19, 'Monday', 'Montag', 'MOHN-tahk', 'https://placehold.co/300x200/000000/FFCC00?text=Montag', 1),
(19, 'Tuesday', 'Dienstag', 'DEEN-stahk', 'https://placehold.co/300x200/000000/FFCC00?text=Dienstag', 2),
(19, 'Wednesday', 'Mittwoch', 'MITT-vock', 'https://placehold.co/300x200/000000/FFCC00?text=Mittwoch', 3),
(19, 'Thursday', 'Donnerstag', 'DON-ner-stahk', 'https://placehold.co/300x200/000000/FFCC00?text=Donnerstag', 4),
(19, 'Friday', 'Freitag', 'FRY-tahk', 'https://placehold.co/300x200/000000/FFCC00?text=Freitag', 5),
(19, 'Saturday', 'Samstag', 'ZAHM-stahk', 'https://placehold.co/300x200/000000/FFCC00?text=Samstag', 6),
(19, 'Sunday', 'Sonntag', 'ZON-tahk', 'https://placehold.co/300x200/000000/FFCC00?text=Sonntag', 7);

-- Insert quiz questions
-- Spanish Greetings quiz questions
INSERT INTO quiz_questions (flashcard_id, question, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) VALUES
(1, 'How do you say "Hello" in Spanish?', 'Hola', 'Adios', 'Gracias', 'Por favor'),
(2, 'What is the Spanish greeting for "Good morning"?', 'Buenos días', 'Buenas tardes', 'Buenas noches', 'Buen provecho'),
(3, 'How would you greet someone in the afternoon in Spanish?', 'Buenas tardes', 'Buenos días', 'Buenas noches', 'Hasta luego'),
(4, 'What is the correct way to say "Good evening" in Spanish?', 'Buenas noches', 'Buenos días', 'Buenas tardes', 'Hasta mañana'),
(5, 'How do you ask "How are you?" in Spanish?', '¿Cómo estás?', '¿Qué tal?', '¿Dónde estás?', '¿Quién eres?');

-- French Greetings quiz questions
INSERT INTO quiz_questions (flashcard_id, question, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) VALUES
(6, 'How do you say "Hello" in French?', 'Bonjour', 'Au revoir', 'Merci', 'S\'il vous plaît'),
(7, 'What is the French greeting for "Good evening"?', 'Bonsoir', 'Bonjour', 'Bonne nuit', 'À demain'),
(8, 'How would you ask "How are you?" in French?', 'Comment allez-vous?', 'Comment t\'appelles-tu?', 'Où êtes-vous?', 'Qui êtes-vous?'),
(9, 'How do you say "My name is..." in French?', 'Je m\'appelle...', 'Je suis...', 'Mon nom est...', 'Je m\'excuse...'),
(10, 'What is the French phrase for "Nice to meet you"?', 'Enchanté', 'Bienvenue', 'Excusez-moi', 'À bientôt');

-- German Greetings quiz questions
INSERT INTO quiz_questions (flashcard_id, question, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) VALUES
(11, 'How do you say "Hello" in German?', 'Hallo', 'Tschüss', 'Danke', 'Bitte'),
(12, 'What is the German greeting for "Good morning"?', 'Guten Morgen', 'Guten Tag', 'Guten Abend', 'Gute Nacht'),
(13, 'How would you greet someone during the day in German?', 'Guten Tag', 'Guten Morgen', 'Guten Abend', 'Auf Wiedersehen'),
(14, 'What is the correct way to say "Good evening" in German?', 'Guten Abend', 'Guten Morgen', 'Guten Tag', 'Gute Nacht'),
(15, 'How do you ask "How are you?" in German?', 'Wie geht es dir?', 'Wie heißt du?', 'Wo bist du?', 'Wer bist du?');

-- Insert achievements
INSERT INTO achievements (name, code, description, icon_url) VALUES
('First Steps', 'first_steps', 'Complete your first lesson', 'https://placehold.co/100x100/58CC02/FFFFFF?text=🚶'),
('Quiz Master', 'quiz_master', 'Score 80% or higher on a quiz', 'https://placehold.co/100x100/58CC02/FFFFFF?text=🏆'),
('Language Explorer', 'language_explorer', 'Try lessons in all available languages', 'https://placehold.co/100x100/58CC02/FFFFFF?text=🌍'),
('Daily Streak', 'daily_streak', 'Learn for 7 consecutive days', 'https://placehold.co/100x100/58CC02/FFFFFF?text=🔥'),
('Perfectionist', 'perfectionist', 'Complete all lessons in a category with 100% quiz score', 'https://placehold.co/100x100/58CC02/FFFFFF?text=⭐');