const axios = require('axios');

const API_URL = 'http://localhost:3000/api';
// Use the same login credentials as previous tests
const LOGIN_CREDENTIALS = {
    email: 'rjbrgudang@saga.com',
    password: 'admin123'
};

async function testSupplierFlow() {
    try {
        console.log('🔑 Logging in...');
        const loginRes = await axios.post(`${API_URL}/auth/login`, LOGIN_CREDENTIALS);
        const token = loginRes.data.data.token;
        console.log('✅ Login successful');

        const headers = { Authorization: `Bearer ${token}` };

        // 1. Create Supplier
        console.log('\n📦 Creating Supplier...');
        const createRes = await axios.post(`${API_URL}/suppliers`, {
            name: 'Test Supplier PT',
            contact_person: 'Budi Santoso',
            phone: '08123456789',
            address: 'Jl. Test No. 123'
        }, { headers });
        console.log('✅ Created Supplier ID:', createRes.data.data.id);
        const supplierId = createRes.data.data.id;

        // 2. List Suppliers
        console.log('\n📋 Listing Suppliers...');
        const listRes = await axios.get(`${API_URL}/suppliers`, { headers });
        const suppliers = listRes.data.data;
        console.log(`✅ Found ${suppliers.length} suppliers`);
        const found = suppliers.find(s => s.id === supplierId);
        if (found) console.log('✅ Verified created supplier exists in list');
        else throw new Error('Created supplier not found in list');

        // 3. Update Supplier
        console.log('\n✏️ Updating Supplier...');
        await axios.put(`${API_URL}/suppliers/${supplierId}`, {
            name: 'Test Supplier PT Updated',
            contact_person: 'Budi Santoso',
            phone: '08123456789',
            address: 'Jl. Baru No. 456'
        }, { headers });
        console.log('✅ Supplier updated');

        // 4. Get Single Supplier
        console.log('\n🔍 Getting Single Supplier...');
        const getRes = await axios.get(`${API_URL}/suppliers/${supplierId}`, { headers });
        if (getRes.data.data.name === 'Test Supplier PT Updated') {
            console.log('✅ Verified update persisted');
        } else {
            throw new Error('Update verification failed');
        }

        // 5. Delete Supplier
        console.log('\n🗑️ Deleting Supplier...');
        await axios.delete(`${API_URL}/suppliers/${supplierId}`, { headers });
        console.log('✅ Supplier deleted');

        // Verify Deletion
        try {
            await axios.get(`${API_URL}/suppliers/${supplierId}`, { headers });
            throw new Error('Supplier should have been deleted but was found');
        } catch (error) {
            if (error.response && error.response.status === 404) {
                console.log('✅ Verified deletion (404 Not Found)');
            } else {
                throw error;
            }
        }

        console.log('\n🎉 Supplier Flow Verification Passed!');

    } catch (error) {
        console.error('❌ Test Failed:', error.response?.data || error.message);
        process.exit(1);
    }
}

testSupplierFlow();
