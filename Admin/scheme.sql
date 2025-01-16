CREATE DATABASE meeting_scheduler;

USE meeting_scheduler;

-- Table to store user information
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
);

-- Table to store meeting information
CREATE TABLE meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    purpose TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table to store time slots for meetings
CREATE TABLE time_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('available', 'booked') DEFAULT 'available',
    FOREIGN KEY (meeting_id) REFERENCES meetings(id)
);
