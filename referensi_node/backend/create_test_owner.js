require('dotenv').config();
const User = require('./models/main/User');
const { getMainPool } = require('./config/database');

async function createTestOwner() {
    try {
        console.log('Creating test owner...');

        // Check if exists
        const existing = await User.findByEmail('owner@test.com');
        if (existing) {
            console.log('Test owner already exists.');
            // Update password just in case
            await User.update(existing.id, { password: 'password123' });
            console.log('Password updated.');
        } else {
            // Create
            await User.create({
                name: 'Test Owner',
                email: 'owner@test.com',
                password: 'password123',
                role: 'tenant_owner',
                tenant_id: 1, // Assuming tenant 1 exists
                is_active: true
            });
            console.log('Test owner created.');
        }
    } catch (err) {
        console.error('Error:', err);
    } finally {
        process.exit(0);
    }
}

createTestOwner();
