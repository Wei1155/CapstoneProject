-- 1. Table to store all available badges in the system (Module 3.1)
CREATE TABLE Badge (
    BadgeID INT PRIMARY KEY AUTO_INCREMENT,
    BadgeName VARCHAR(100) NOT NULL,
    PointsRequired INT NOT NULL
);

-- 2. Table to track which student has earned which badge (Module 3.3)
CREATE TABLE Student_Badge (
    RecordID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT NOT NULL,
    BadgeID INT NOT NULL,
    DateEarned DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID),
    FOREIGN KEY (BadgeID) REFERENCES Badge(BadgeID)
);

-- 3. Table to keep a history of everything the student does (Module 3.4)
CREATE TABLE Activity_Log (
    LogID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT NOT NULL,
    ActionDescription VARCHAR(255) NOT NULL,
    Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
);

-- 4. Tell your leader to ADD these two columns to their existing Student table:
-- ALTER TABLE Student ADD COLUMN TotalPoints INT DEFAULT 0;
-- ALTER TABLE Student ADD COLUMN CurrentLevel INT DEFAULT 1;