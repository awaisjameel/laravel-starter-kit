import { execSync } from 'child_process';
import semver from 'semver';

const userAgent = process.env.npm_config_user_agent;

// 1. Check for npm as the package manager
if (!userAgent || !userAgent.startsWith('npm/')) {
    console.error('Error: Please use npm as the package manager for this project.');
    process.exit(1);
}

// 2. Define the required engine versions directly in the script
//    (Alternatively, you could read these from package.json, but for a simple check, hardcoding is fine)
const requiredNodeVersion = '24.1.0';
const requiredNpmVersion = '11.2.1';

// 3. Get current Node.js version
const currentNodeVersion = process.versions.node;

// 4. Get current npm version
//    npm_execpath contains the path to npm cli, we can use it to get npm version
let currentNpmVersion;
try {
    currentNpmVersion = execSync('npm -v', { encoding: 'utf8' }).trim();
} catch {
    console.error('Error: Could not determine npm version. Is npm installed correctly?');
    process.exit(1);
}

// 5. Compare versions
if (semver.lt(currentNodeVersion, requiredNodeVersion)) {
    console.error(`Error: Your Node.js version (${currentNodeVersion}) is too old.`);
    console.error(`Please upgrade to Node.js v${requiredNodeVersion} or higher.`);
    process.exit(1);
}

if (semver.lt(currentNpmVersion, requiredNpmVersion)) {
    console.error(`Error: Your npm version (${currentNpmVersion}) is too old.`);
    console.error(`Please upgrade to npm v${requiredNpmVersion} or higher.`);
    process.exit(1);
}

console.log('Using npm as expected with compatible Node.js and npm versions.');
