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

CREATE TABLE IF NOT EXISTS competitions (
    	competitionId INT NOT NULL,
    	season INT NOT NULL,
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
