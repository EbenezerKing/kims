CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    award_date DATE NOT NULL,
    award_no VARCHAR(100) NOT NULL,
    details TEXT NOT NULL,
    waybill_no VARCHAR(100),
    invoice_no VARCHAR(100),
    quantity_ordered INT,
    quantity_received INT,
    unit_of_count INT,
    balance VARCHAR(50),
    batch_no VARCHAR(100),
    expiry_date DATE,
    price DECIMAL(10,2),
    amount DECIMAL(10,2),
    sra_no VARCHAR(50),
    sra_date DATE,
    lpo_no VARCHAR(100),
    status ENUM('Pending','Complete') NOT NULL,
    attachment_path VARCHAR(255),
    submitted_by INT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submitted_by) REFERENCES users(id)
);

CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE purchases
ADD COLUMN created_by INT NOT NULL AFTER attachment_path,
ADD FOREIGN KEY (created_by) REFERENCES users(id);


INSERT INTO users (username, password, role) VALUES ('admin', '$2b$12$f0xkhSwTYi5pI5PAo9ydkeCsJ/d1AuWG7ctuPwrZT7aOmQTPI8fau', 'admin');