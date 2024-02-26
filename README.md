# wikipedAI

generates wikipedia-esque pages on the fly using AI, turning *EVERY* word into a link.

## developing
run ollama locally (install mistral first, see ollama documentation):
```sh
$ ollama serve
```

run postgres
```sh
$ LC_ALL="C" postgres -D /opt/homebrew/var/postgresql@16
```

run php dev server
```sh
$ php -S 0.0.0.0:8080
```

open browser at http://localhost:8080

