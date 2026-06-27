let fs = require('fs')
let { execSync } = require('child_process')

let latest = execSync('npm view alpinejs version').toString().trim()

console.log(`Latest Alpine.js version: ${latest}`)

let pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'))

let current = pkg.dependencies['alpinejs'].replace('^', '')

if (current === latest) {
    console.log('Already up to date.')
    process.exit(0)
}

console.log(`Updating from ${current} to ${latest}...`)

for (let dep of Object.keys(pkg.dependencies)) {
    if (dep === 'alpinejs' || dep.startsWith('@alpinejs/')) {
        pkg.dependencies[dep] = `^${latest}`
    }
}

fs.writeFileSync('package.json', JSON.stringify(pkg, null, 4) + '\n')

console.log('Running npm install...')
execSync('npm install', { stdio: 'inherit' })

console.log(`\nDone. Alpine updated to ^${latest}. Don't forget to commit package.json and package-lock.json.`)
