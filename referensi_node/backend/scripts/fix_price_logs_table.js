const mysql = require('mysql2/promise');

async function fixTables() {
    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: ''
    });

    try {
        console.log('--- Fixing Missing Tables ---');

        // Get all tenant databases
        const [dbs] = await connection.execute("SHOW DATABASES LIKE 'saga_tenant_%'");
        const databases = dbs.map(d => Object.values(d)[0]);

        console.log(`Found ${databases.length} tenant databases.`);

        for (const dbName of databases) {
            console.log(`Checking ${dbName}...`);
            await connection.changeUser({ database: dbName });

            // Create price_logs table
            await connection.execute(`
                CREATE TABLE IF NOT EXISTS price_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    product_id INT NOT NULL,
                    unit_id INT NOT NULL,
                    user_id INT NULL,
                    old_price DECIMAL(15,2) NOT NULL,
                    new_price DECIMAL(15,2) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_created_at (created_at),
                    INDEX idx_product (product_id)
                ) ENGINE=InnoDB;
            `);
            console.log(`  -> price_logs checked/created.`);

            // Create notification_states table (just in case)
            await connection.execute(`
                 CREATE TABLE IF NOT EXISTS notification_states (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    notification_key VARCHAR(255) NOT NULL,
                    is_read BOOLEAN DEFAULT FALSE,
                    user_id INT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_key_user (notification_key, user_id)
                ) ENGINE=InnoDB;
            `);
            console.log(`  -> notification_states checked/created.`);
        }

        console.log('All databases fixed.');

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await connection.end();
    }
}

fixTables();
