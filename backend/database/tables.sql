/* Stores basic FIE Athlete Data */
CREATE TABLE IF NOT EXISTS athletes (
    	id INT PRIMARY KEY,
    	name VARCHAR(100) NOT NULL,
    	firstName VARCHAR(50) NOT NULL,
    	lastName VARCHAR(50) NOT NULL,
    	gender ENUM('male', 'female') NOT NULL,
    	nationality CHAR(3) NOT NULL,
    	weapon ENUM('sabre', 'epee', 'foil') NOT NULL,
    	weapon2 ENUM('sabre', 'epee', 'foil') DEFAULT NULL,
    	isActive BOOLEAN DEFAULT FALSE
);
/* Stores Competition Data */
CREATE TABLE IF NOT EXISTS competitions (
    	season INT NOT NULL,
    	competitionId INT NOT NULL,
    	name VARCHAR(100) NOT NULL,
    	category VARCHAR(5) NOT NULL,
    	weapon ENUM('sabre', 'epee', 'foil') NOT NULL,
   	gender ENUM('male', 'female') NOT NULL,
    	country CHAR(3) NOT NULL,
    	location VARCHAR(50) NOT NULL,
    	startDate DATE NOT NULL,
    	endDate DATE,
    	PRIMARY KEY (competitionId, season) 
);
/* Stores Competition Results */
CREATE TABLE IF NOT EXISTS competitionResults (
    	season INT NOT NULL,
    	competitionId INT NOT NULL,
    	athleteId INT NOT NULL,
    	finished INT NOT NULL,
    	points DOUBLE NOT NULL,
    	PRIMARY KEY (competitionId, season, athleteId),
    	FOREIGN KEY (competitionId, season) REFERENCES competitions(competitionId, season) ON DELETE CASCADE,
   	FOREIGN KEY (athleteId) REFERENCES athletes(id) ON DELETE CASCADE
);
/* Stores Total Points Earned by Athlete per Season and Weapon*/
CREATE TABLE IF NOT EXISTS athleteSeasonPoints (
    	athleteId INT NOT NULL,
    	season INT NOT NULL,
    	weapon ENUM('sabre', 'epee', 'foil') NOT NULL,
    	points DOUBLE NOT NULL DEFAULT 0,
    	PRIMARY KEY (athleteId, season, weapon),
    	FOREIGN KEY (athleteId) REFERENCES athletes(id) ON DELETE CASCADE
);

/* Stores users account info */
CREATE TABLE IF NOT EXISTS users (
    	id INT AUTO_INCREMENT PRIMARY KEY,
    	username VARCHAR(50) UNIQUE NOT NULL,
    	email VARCHAR(100) NOT NULL,
    	nationality CHAR(3) NOT NULL,
    	password VARCHAR(255) NOT NULL,
    	isAdmin BOOLEAN DEFAULT FALSE,
    	CONSTRAINT chk_password CHECK (CHAR_LENGTH(password) >= 8),
    	CONSTRAINT chk_email_format CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);
