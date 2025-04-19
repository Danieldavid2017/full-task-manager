-- Crear las tablas
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name CHARACTER VARYING(10),
    email CHARACTER VARYING(30),
    password CHARACTER VARYING(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE category (
    id SERIAL PRIMARY KEY,
    name CHARACTER VARYING(15) NOT NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE task (
    id BIGSERIAL PRIMARY KEY,
    title CHARACTER VARYING(15),
    description TEXT,
    due_date DATE,
    complete BOOLEAN DEFAULT FALSE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES category(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar algunos datos de ejemplo
INSERT INTO users (name, email, password) 
VALUES ('Uldarico', 'uldarico@ejemplo.com', '1234');

INSERT INTO category (name, user_id) 
VALUES ('Trabajo', 1), ('Personal', 1);

INSERT INTO task (title, description, due_date, user_id, category_id) 
VALUES 
('Finalizar ', 'Completar el informe', '2025-04-20', 1, 1),
('Comprar víveres', 'Ir al supermercado', '2025-04-18', 1, 2);

-- Consulta para obtener tareas con sus categorías y usuarios
SELECT 
    t.id,
    t.title,
    t.description,
    t.due_date,
    t.complete,
    c.name AS category_name,
    u.name AS user_name
FROM 
    task t
JOIN 
    category c ON t.category_id = c.id
JOIN 
    users u ON t.user_id = u.id
WHERE 
    t.complete = FALSE
ORDER BY 
    t.due_date ASC;