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
    	fencerId INT NOT NULL,
    	finished INT NOT NULL,
    	points DOUBLE NOT NULL,
    	PRIMARY KEY (competitionId, season, fencerId),
    	FOREIGN KEY (competitionId, season) REFERENCES competitions(competitionId, season) ON DELETE CASCADE,
   	FOREIGN KEY (fencerId) REFERENCES athletes(id) ON DELETE CASCADE
);
