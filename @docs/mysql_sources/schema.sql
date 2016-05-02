CREATE TABLE IF NOT EXISTS tests (
    id INTEGER(10) AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created DATETIME NOT NULL,
    passed TINYINT DEFAULT 0 NOT NULL,
    price FLOAT DEFAULT 0 NOT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS test_questions (
    id INTEGER(10) AUTO_INCREMENT NOT NULL,
    value VARCHAR(255) NOT NULL,
    test_id INTEGER(10)  NOT NULL,
    `index` INTEGER(10) NOT NULL,
    multioption TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS test_options (
    id INTEGER(10) AUTO_INCREMENT NOT NULL,
    value VARCHAR(255) NOT NULL,
    points INTEGER(10) NOT NULL DEFAULT 0,
    question_id INTEGER(10)  NOT NULL,
    `index` INTEGER(10) NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (question_id) REFERENCES test_questions(id) ON DELETE CASCADE
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS test_results (
    id INTEGER(10) AUTO_INCREMENT NOT NULL,
    test_id INTEGER(10)  NOT NULL,
    min_points INTEGER(10)  NOT NULL,
    max_points INTEGER(10)  NOT NULL,
    title VARCHAR(255),
    description TEXT NOT NULL,
    variant VARCHAR(10) DEFAULT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER(10) AUTO_INCREMENT NOT NULL ,
    username VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(64) NOT NULL,
    email VARCHAR(100) NOT NULL,
    wallet FLOAT DEFAULT 0,
    registered_at DATETIME NOT NULL,
    activation_code VARCHAR(255) NOT NULL,
    active TINYINT DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

# Relation between users and test results
CREATE TABLE IF NOT EXISTS passed_tests (
    user_id INTEGER(10) NOT NULL,
    test_id INTEGER(10) NOT NULL,
    points INTEGER(10) NOT NULL DEFAULT 0,
    result_id INTEGER(10) NOT NULL,
    test_data BLOB NOT NULL,
    passed_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, test_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (test_id) REFERENCES tests(id)
) ENGINE = InnoDb DEFAULT CHARSET = UTF8;

# Many to Many: Tests-Users
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER(10) AUTO_INCREMENT NOT NULL,
    price FLOAT NOT NULL,
    order_date DATETIME NOT NULL,
    customer_id INTEGER(10) NOT NULL,
    test_id INTEGER(10) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (test_id) REFERENCES tests (id)
);