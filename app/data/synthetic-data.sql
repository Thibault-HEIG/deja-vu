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
    INSERT INTO users (id, name, email, password, last_color_harmony_importance, last_season_harmony_importance) 
  VALUES                                                                                                         
    (1, 'Alice', 'alice.martin@example.com', '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.                 
  G/Jk2X7x9wL40mYqK', 0.75, 0.25),                                                                               
    (2, 'Bob',   'bob.dupont@example.com',   '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.                 
  G/Jk2X7x9wL40mYqK', 0.15, 0.85),                                                                               
    (3, 'Chloé',  'chloe.viret@example.com',  '$2y$12$e8Yk1m4gA27YnIuN2xZ/g.wZkQ52kC76F8M.
  G/Jk2X7x9wL40mYqK', 0.60, 0.40);
  
    -- --------------------------------------------------------
    -- 3. Vêtements (clothes)
    -- Catégories : 'top', 'bottom', 'shoes', 'jacket', 'accessory'
    -- Teinte (hue) : [0-360], Saturation / Luminosité (brightness) : [0-1]
    -- --------------------------------------------------------
    INSERT INTO clothes (id, img_url, category, season_id, hue, saturation, brightness, user_rate, user_id)      
  VALUES
    -- Hauts (tops)
    (1,  'img/uploads/user_1/item_1.jpg',   'top',       2,   0.0, 0.00, 0.95, 5, 1),
    (2,  'img/uploads/user_1/item_2.jpg',   'top',       1, 215.0, 0.65, 0.35, 4, 1),
    (3,  'img/uploads/user_1/item_3.jpg',   'top',       4,  38.0, 0.25, 0.82, 5, 1),
    (4,  'img/uploads/user_2/item_4.jpg',   'top',       3,   0.0, 0.00, 0.12, 4, 2),
  
    -- Bas (bottoms)
    (5,  'img/uploads/user_1/item_5.jpg',   'bottom',    3, 220.0, 0.55, 0.28, 5, 1),
    (6,  'img/uploads/user_1/item_6.jpg',   'bottom',    1,  42.0, 0.32, 0.78, 4, 1),
    (7,  'img/uploads/user_2/item_7.jpg',   'bottom',    4,   0.0, 0.00, 0.15, 4, 2),
    (8,  'img/uploads/user_2/item_8.jpg',   'bottom',    2,  48.0, 0.18, 0.88, 3, 2),
  
    -- Chaussures (shoes)
    (9,  'img/uploads/user_1/item_9.jpg',   'shoes',     2,   0.0, 0.00, 0.98, 5, 1),
    (10, 'img/uploads/user_1/item_10.jpg',  'shoes',     4,  28.0, 0.58, 0.32, 4, 1),
    (11, 'img/uploads/user_2/item_11.jpg',  'shoes',     3,   0.0, 0.00, 0.10, 4, 2),
  
    -- Vestes / Manteaux (jackets)
    (12, 'img/uploads/user_1/item_12.jpg',  'jacket',    1, 210.0, 0.45, 0.42, 4, 1),
    (13, 'img/uploads/user_1/item_13.jpg',  'jacket',    4, 205.0, 0.12, 0.22, 5, 1),
    (14, 'img/uploads/user_2/item_14.jpg',  'jacket',    3, 140.0, 0.40, 0.35, 3, 2),
  
    -- Accessoires (accessories)
    (15, 'img/uploads/user_1/item_15.jpg',  'accessory', 1,  30.0, 0.60, 0.25, 4, 1),
    (16, 'img/uploads/user_1/item_16.jpg',  'accessory', 4,   0.0, 0.00, 0.60, 5, 1),
    (17, 'img/uploads/user_2/item_17.jpg',  'accessory', 4, 350.0, 0.45, 0.30, 4, 2);
  
    -- --------------------------------------------------------
    -- 4. Tenues générées (results)
    -- --------------------------------------------------------
    INSERT INTO results (id, id_top, id_bottom, id_shoes, id_jacket, id_accessory, timestamp) VALUES             
    (1, 1, 5,  9, 12,   15, '2026-04-12 10:15:00'),
    (2, 3, 5, 10, 13,   16, '2026-01-20 08:45:00'),
    (3, 2, 6,  9, NULL, 15, '2026-05-02 18:20:00'),
    (4, 4, 7, 11, 14,   17, '2026-10-10 14:05:00');
