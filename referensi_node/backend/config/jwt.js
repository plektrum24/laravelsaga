module.exports = {
    secret: process.env.JWT_SECRET || 'default-secret-change-me',
    expiresIn: process.env.JWT_EXPIRES_IN || '365d', // Extended to 1 year for desktop app
    algorithm: 'HS256'
};
