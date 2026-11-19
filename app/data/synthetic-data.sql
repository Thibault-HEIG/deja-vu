    USE clothes_match;                                                                                           
                                                                                                                 
    -- --------------------------------------------------------                                                  
    -- 1. Saisons                                                                                                
    -- --------------------------------------------------------                                                  
    INSERT INTO seasons (id, name, avg_temperature, icon_url) VALUES                                             
    (1, 'Printemps', 20, '/icons/seasons/spring.svg'),                                                                      
    (2, 'Été', 30, '/icons/seasons/summer.svg'),                                                                      
    (3, 'Automne', 15, '/icons/seasons/autumn.svg'),                                                                      
    (4, 'Hiver',  5, '/icons/seasons/winter.svg');                                                                                                                               
                                                                                                                 
    -- --------------------------------------------------------                                                  
    -- 2. Utilisateurs                                                                                           
    -- (password = "password123" hashé via bcrypt)                                                               
    -- --------------------------------------------------------                                                  
    INSERT INTO users (id, name, email, password, secret_token) 
  VALUES                                                                                                         
    (1, 'Alice', 'alice.martin@example.com', '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.                 
  G/Jk2X7x9wL40mYqK', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4'),                                                                               
    (2, 'Bob',   'bob.dupont@example.com',   '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.                 
  G/Jk2X7x9wL40mYqK', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5'),                                                                               
    (3, 'Chloé',  'chloe.viret@example.com',  '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.
  G/Jk2X7x9wL40mYqK', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6');
    (4, 'Loana',  'loana.babey@heig-vd.ch',  '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.
  G/Jk2X7x9wL40mYqK', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p7');
  
    -- --------------------------------------------------------
    -- 3. Vêtements (clothes)
    -- Catégories : 'top', 'bottom', 'shoes', 'jacket', 'accessory'
    -- Teinte (hue) : [0-360], Saturation / Luminosité (brightness) : [0-1]
    -- --------------------------------------------------------
    INSERT INTO clothes (id, img_url, category, season_id, hue, saturation, brightness, user_rate, user_id)      
  VALUES
    -- Hauts (tops)
    (1,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_1.png',   'top',       2,   0, 00, 95, 5, 1),
    (2,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_2.png',   'top',       1, 215.0, 65, 35, 4, 1),
    (3,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_3.png',   'top',       4,  38.0, 25, 82, 5, 1),
    (4,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_4.png',   'top',       3,   0, 00, 12, 4, 2),
  
    -- Bas (bottoms)
    (5,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_5.png',   'bottom',    3, 220, 55, 28, 5, 1),
    (6,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_6.png',   'bottom',    1,  42.0, 32, 78, 4, 1),
    (7,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_7.png',   'bottom',    4,   0, 00, 15, 4, 2),
    (8,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_8.png',   'bottom',    2,  48.0, 18, 88, 3, 2),
  
    -- Chaussures (shoes)
    (9,  'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_9.png',   'shoes',     2,   0, 00, 98, 5, 1),
    (10, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_10.png',  'shoes',     4,  28.0, 58, 32, 4, 1),
    (11, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_11.png',  'shoes',     3,   0, 00, 10, 4, 2),
  
    -- Vestes / Manteaux (jackets)
    (12, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_12.png',  'jacket',    1, 210, 45, 42, 4, 1),
    (13, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_13.png',  'jacket',    4, 205.0, 12, 22, 5, 1),
    (14, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_14.png',  'jacket',    3, 140, 40, 35, 3, 2),
  
    -- Accessoires (accessories)
    (15, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_15.png',  'accessory', 1,  30, 60, 25, 4, 1),
    (16, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p4/item_16.png',  'accessory', 4,   0, 00, 60, 5, 1),
    (17, 'img/uploads/a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p5/item_17.png',  'accessory', 4, 350, 45, 30, 4, 2);
  
    -- --------------------------------------------------------
    -- 4. Tenues générées (results)
    -- --------------------------------------------------------
    INSERT INTO results (id, id_top, id_bottom, id_shoes, id_jacket, id_accessory, timestamp, season_harmony, seed_item_id, season_wish_id) VALUES             
    (1, 1, 5,  9, 12,   15, '2026-04-12 10:15:00', 0.25, 1,    1),
    (2, 3, 5, 10, 13,   16, '2026-01-20 08:45:00', 0.85, NULL, 4),
    (3, 2, 6,  9, NULL, 15, '2026-05-02 18:20:00', 0.40, 2,    1),
    (4, 4, 7, 11, 14,   17, '2026-10-10 14:05:00', 0.50, NULL, 3);
