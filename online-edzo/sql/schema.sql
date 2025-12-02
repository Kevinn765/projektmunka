CREATE DATABASE IF NOT EXISTS online_edzo DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE online_edzo;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  age INT DEFAULT NULL,
  weight FLOAT DEFAULT NULL,
  goal VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exercises (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  muscle_group VARCHAR(100) NOT NULL,
  description TEXT,
  tips TEXT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(150),
  data JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT,
  answer TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO exercises (name, muscle_group, description, tips) VALUES
('Fekvenyomás','Mell','Fekvő pozícióból súlyzóval vagy rúddal végzett gyakorlat.','Tartsd feszesen a core-t.'),
('Guggolás','Láb','Alap lábgyakorlat rúddal a vállon.','Ne hajlítsd túl a térdet előre.');
