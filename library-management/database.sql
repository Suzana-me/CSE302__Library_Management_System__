-- Database Creation
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- 1. Departments
CREATE TABLE IF NOT EXISTS departments (
    dept_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL UNIQUE
);

-- Sample departments
INSERT INTO departments (dept_name) VALUES
('CSE'),
('EEE'),
('BBA'),
('English'),
('Sociology'),
('Economics'),
('PPHS'),
('Civil')
ON DUPLICATE KEY UPDATE dept_name=dept_name;

-- 2. Books
CREATE TABLE IF NOT EXISTS books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    dept_id INT,
    isbn VARCHAR(50),
    quantity INT DEFAULT 1,
    FOREIGN KEY (dept_id) REFERENCES departments(dept_id)
);

-- Sample Books
INSERT INTO books (title, author, dept_id, isbn, quantity) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', 1, '9780262033848', 5),
('Clean Code', 'Robert C. Martin', 1, '9780132350884', 3),
('Basic Electrical Engineering', 'V.K. Mehta', 2, '9788121908629', 4),
('Principles of Marketing', 'Philip Kotler', 3, '9780132167123', 2);

-- 3. Members
CREATE TABLE IF NOT EXISTS members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    joined_date DATE DEFAULT CURRENT_DATE
);

-- Sample Members
INSERT INTO members (name, email, password, phone, address) VALUES
('John Doe', 'john@example.com', '123456', '1234567890', '123 Main St'),
('Jane Smith', 'jane@example.com', '123456', '0987654321', '456 Oak Ave');

-- 4. Librarians
CREATE TABLE IF NOT EXISTS librarians (
    librarian_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Sample Librarian
INSERT INTO librarians (name, username, password) VALUES
('Main Librarian', 'lib1', 'lib123');

-- 5. Admins
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Sample Admin
INSERT INTO admins (username, password) VALUES
('admin', 'admin123');

-- 6. Issued Books
CREATE TABLE IF NOT EXISTS issued_books (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    issue_date DATE DEFAULT CURRENT_DATE,
    due_date DATE NOT NULL,
    return_date DATE,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (member_id) REFERENCES members(member_id)
);

-- 7. Fines
CREATE TABLE IF NOT EXISTS fines (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (issue_id) REFERENCES issued_books(issue_id)
);
