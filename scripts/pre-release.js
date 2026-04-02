let fs = require('fs')
let { execSync } = require('child_process')

let version = process.argv[2]
let alpineVersion = process.argv[3]

if (! version) {
    console.error('Usage: node scripts/pre-release.js <version> [alpine-version]')
    console.error('Example: node scripts/pre-release.js 4.3.0 3.15.11')
    process.exit(1)
}

if (!/^\d+\.\d+\.\d+(-[\w.]+)?$/.test(version)) {
    console.error(`Invalid version format: ${version}`)
    process.exit(1)
}

console.log(`Preparing Livewire v${version}...`)

// Update Alpine versions if specified
if (alpineVersion) {
    if (!/^\d+\.\d+\.\d+(-[\w.]+)?$/.test(alpineVersion)) {
        console.error(`Invalid Alpine version format: ${alpineVersion}`)
        process.exit(1)
    }

    console.log(`Updating Alpine.js to ^${alpineVersion}...`)

    let pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'))

    for (let dep of Object.keys(pkg.dependencies)) {
        if (dep === 'alpinejs' || dep.startsWith('@alpinejs/')) {
            pkg.dependencies[dep] = `^${alpineVersion}`
        }
    }

    fs.writeFileSync('package.json', JSON.stringify(pkg, null, 4) + '\n')
    console.log('Updated package.json')
}

// Install dependencies
console.log('Running npm install...')
run('npm install')

// Build
console.log('Building assets...')
run('npm run build')

// Test
console.log('Running JS tests...')
run('npm run test:run')

console.log(`\nPre-release prep complete for v${version}.`)

function run(cmd) {
    execSync(cmd, { stdio: 'inherit' })
}
