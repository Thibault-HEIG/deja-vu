USE clothes_match;

CREATE TABLE IF NOT EXISTS seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    avg_temperature INT NOT NULL,
    icon_url VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    secret_token VARCHAR(32) NOT NULL UNIQUE
);



CREATE TABLE IF NOT EXISTS clothes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    img_url VARCHAR(500) NOT NULL,
    category VARCHAR(20) NOT NULL,
    season_id INT NOT NULL,
    hue INT NOT NULL, -- [0,360]
    saturation INT NOT NULL, -- [0,100]
    brightness INT NOT NULL, -- [0,100]
    user_rate INT NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (season_id) REFERENCES seasons (id)
);

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_top INT NOT NULL,
    id_bottom INT NOT NULL,
    id_shoes INT NOT NULL,
    id_jacket INT,
    id_accessory INT,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    season_harmony DOUBLE NOT NULL, -- [0,1]
    -- color_harmony = (1 - season_harmony)
    seed_item_id INT,
    season_wish_id INT,
    FOREIGN KEY (id_top) REFERENCES clothes (id),
    FOREIGN KEY (id_bottom) REFERENCES clothes (id),
    FOREIGN KEY (id_shoes) REFERENCES clothes (id),
    FOREIGN KEY (id_jacket) REFERENCES clothes (id),
    FOREIGN KEY (id_accessory) REFERENCES clothes (id),
    FOREIGN KEY (seed_item_id) REFERENCES clothes (id),
    FOREIGN KEY (season_wish_id) REFERENCES seasons (id)
);
