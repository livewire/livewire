const { toString, hasOwnProperty } = Object.prototype
const OBJECT_TYPE = "[object Object]"
const ARRAY_TYPE = "[object Array]"

export default {
    join(path, key) {
        return path != null ? path + "[" + key + "]" : key
    },

    flatten(obj, path, result) {
        const type = toString.call(obj)

        if (result === undefined) {
            if (type === OBJECT_TYPE) {
                result = {}
            } else if (type === ARRAY_TYPE) {
                result = []
            } else {
                return
            }
        }

        for (const key in obj) {
            if (! hasOwnProperty.call(obj, key)) {
                continue
            }

            const val = obj[key]
            if (val == null) {
                continue
            }

            switch (toString.call(val)) {
                case ARRAY_TYPE:
                case OBJECT_TYPE:
                    this.flatten(val, this.join(path, key), result)
                    break
                default:
                    result[this.join(path, key)] = val
                    break
            }
        }

        return result
    },

    stringify(obj) {
        let parts = this.flatten(obj)

        return Object
            .keys(parts)
            .map(key => `${key}=${parts[key]}`)
            .join('&')
    },

    parse(query) {
        let obj = {}
        if (query.slice(0, 1) === '?') {
            query = query.slice(1)
        }
        query = query.split('&')

        query.map(part => {
            let parts = part.split('=')
            let key = parts[0]
            let value = parts[1]

            parts = key.split('[')
            parts = parts.map(part => part.replace(']', ''))

            if (parts.length > 1) {
                let endValue = value
                value = {}
                parts.forEach((part, index) => {
                    if (index > 1) {
                        if (parts.length == (index + 1)) {
                            value[part] = endValue
                        } else {
                            value[part] = {}
                        }
                    }
                })
            }

            obj[parts[0]] = value
        })

        return obj
    }
}
