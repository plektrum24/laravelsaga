// Run migration for units system
const mysql = require('mysql2/promise');
require('dotenv').config();

async function migrate() {
  const connection = await mysql.createConnection({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'saga_tenant_jkt001',
    multipleStatements: true
  });

  console.log('Connected to database...');

  try {
    // Add prefix column to categories
    await connection.execute("ALTER TABLE categories ADD COLUMN prefix VARCHAR(5)").catch(() => { });
    console.log('✓ Added prefix to categories');

    // Update existing categories with auto-prefixes
    await connection.execute("UPDATE categories SET prefix = UPPER(SUBSTRING(name, 1, 2)) WHERE prefix IS NULL");
    console.log('✓ Updated category prefixes');

    // Create units table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS units (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        sort_order INT NOT NULL
      )
    `);
    console.log('✓ Created units table');

    // Insert units
    const [existing] = await connection.execute('SELECT COUNT(*) as cnt FROM units');
    if (existing[0].cnt === 0) {
      await connection.execute(`
        INSERT INTO units (id, name, sort_order) VALUES
        (1, 'Dus', 1), (2, 'Bal', 2), (3, 'Karung', 3), (4, 'Ikat', 4),
        (5, 'Tim', 5), (6, 'Pack', 6), (7, 'Pcs', 7), (8, 'Btl', 8),
        (9, 'Bks', 9), (10, 'Kg', 10), (11, '1/2 Kg', 11), (12, '1/4 Kg', 12), (13, 'Ons', 13)
      `);
      console.log('✓ Inserted default units');
    }

    // Create sku_sequences table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS sku_sequences (
        category_id INT NOT NULL,
        year INT NOT NULL,
        last_number INT DEFAULT 0,
        PRIMARY KEY (category_id, year)
      )
    `);
    console.log('✓ Created sku_sequences table');

    // Add base_unit_id to products
    await connection.execute("ALTER TABLE products ADD COLUMN base_unit_id INT").catch(() => { });
    console.log('✓ Added base_unit_id to products');

    // Modify stock to decimal
    await connection.execute("ALTER TABLE products MODIFY COLUMN stock DECIMAL(15,4) DEFAULT 0").catch(() => { });
    await connection.execute("ALTER TABLE products MODIFY COLUMN min_stock DECIMAL(15,4) DEFAULT 5").catch(() => { });
    console.log('✓ Modified stock columns to decimal');

    // Create product_units table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS product_units (
        id INT PRIMARY KEY AUTO_INCREMENT,
        product_id INT NOT NULL,
        unit_id INT NOT NULL,
        conversion_qty DECIMAL(15,4) NOT NULL DEFAULT 1,
        buy_price DECIMAL(15,2) DEFAULT 0,
        sell_price DECIMAL(15,2) DEFAULT 0,
        is_base_unit BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id)
      )
    `);
    console.log('✓ Created product_units table');

    // Create index
    await connection.execute("CREATE INDEX idx_product_units_product ON product_units(product_id)").catch(() => { });

    // Migrate existing products to Pcs unit
    const [productsWithoutUnits] = await connection.execute(`
      SELECT p.id, p.buy_price, p.sell_price 
      FROM products p 
      LEFT JOIN product_units pu ON p.id = pu.product_id 
      WHERE pu.id IS NULL
    `);

    for (const p of productsWithoutUnits) {
      await connection.execute(`
        INSERT INTO product_units (product_id, unit_id, conversion_qty, buy_price, sell_price, is_base_unit, sort_order)
        VALUES (?, 7, 1, ?, ?, TRUE, 0)
      `, [p.id, p.buy_price || 0, p.sell_price || 0]);
    }
    console.log(`✓ Migrated ${productsWithoutUnits.length} products to Pcs unit`);

    // Update products base_unit_id
    await connection.execute("UPDATE products SET base_unit_id = 7 WHERE base_unit_id IS NULL");
    console.log('✓ Updated products base_unit_id');

    // Add more categories
    const newCats = [['Makanan', 'MA'], ['Minuman', 'MI'], ['Sembako', 'SE'], ['Snack', 'SN'],
    ['Rokok', 'RO'], ['ATK', 'AT'], ['Elektronik', 'EL'], ['Kosmetik', 'KO'], ['Obat', 'OB']];
    for (const [name, prefix] of newCats) {
      await connection.execute("INSERT IGNORE INTO categories (name, prefix) VALUES (?, ?)", [name, prefix]).catch(() => { });
    }
    console.log('✓ Added new categories');

    console.log('\n✅ Migration completed successfully!');
  } catch (error) {
    console.error('Migration error:', error);
  } finally {
    await connection.end();
  }
}

migrate();
