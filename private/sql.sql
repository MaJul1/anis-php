DROP DATABASE anis;

CREATE DATABASE anis;

USE anis;

CREATE TABLE `User`
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255) UNIQUE,
    IsDeleted TINYINT(1) DEFAULT 0,
    Password VARCHAR(255)
);

CREATE TABLE Product
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255),
    Price DECIMAL(10, 2),
    Unit VARCHAR(10),
    QuantityPerUnit INT DEFAULT 0,
    Archived TINYINT(1) DEFAULT 0,
    CurrentStockNumber INT DEFAULT 0,
    OutOfStockWarningThreshold INT,
    ExpirationWarningThreshold INT,
    OwnerId INT,
    FOREIGN KEY (OwnerId) REFERENCES `User`(Id)
);

CREATE TABLE Restock 
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    CreatedDate DATE DEFAULT(CURRENT_DATE),
    OwnerId INT,
    FOREIGN KEY (OwnerId) REFERENCES `User`(Id)
);

CREATE TABLE RestockDetail 
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    ExpirationDate DATE,
    Count INT,
    IsExpiredChecked TINYINT(1) DEFAULT 0 NULL,
    ProductId INT,
    RestockId INT,
    FOREIGN KEY (ProductId) REFERENCES Product(Id),
    FOREIGN KEY (RestockId) REFERENCES Restock(Id)
);

CREATE TABLE StockOut
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    CreatedDate DATE DEFAULT (CURRENT_DATE),
    OwnerId INT,
    FOREIGN KEY (OwnerId) REFERENCES `User`(Id)
);

CREATE TABLE StockOutDetail
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    StockOutCount INT,
    ProductId INT,
    StockOutId INT,
    FOREIGN KEY (ProductId) REFERENCES Product(Id),
    FOREIGN KEY (StockOutId) REFERENCES StockOut(Id)
);