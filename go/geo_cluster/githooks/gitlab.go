package githooks

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
)

// GitlabRepository represents repository information from the webhook
type GitlabRepository struct {
	Name        string
	URL         string
	Description string
	Home        string
}

// Commit represents commit information from the webhook
type Commit struct {
	ID        string
	Message   string
	Timestamp string
	URL       string
	Author    Author
}

// Author represents author information from the webhook
type Author struct {
	Name  string
	Email string
}

// Webhook represents push information from the webhook
type Webhook struct {
	Before            string
	After             string
	Ref               string
	Username          string
	UserID            int
	ProjectID         int
	Repository        GitlabRepository
	Commits           []Commit
	TotalCommitsCount int
}

func HandlerGitlab(w http.ResponseWriter, r *http.Request) {
	// If the file doesn't exist, create it, or append to the file
	f, err := os.OpenFile("gitlab.log", os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		fmt.Println(err)
		return
	}

	if _, err = f.Write([]byte("started webhook\n")); err != nil {
		fmt.Println(err)
	}
	fmt.Println("start")

	var hook Webhook
	signature := r.Header.Get("X-Gitlab-Token")
	gitlabKey := os.Getenv("GITLAB_TOKEN")
	if gitlabKey != signature {
		if _, err = f.Write([]byte("key fail\n")); err != nil {
			fmt.Println(err)
		}
		return
	}

	//read request body
	var data []byte
	data, err = ioutil.ReadAll(r.Body)
	if err != nil {
		if _, err = f.Write([]byte("Failed to read request:\n" + err.Error())); err != nil {
			fmt.Println(err)
		}
		return
	}
	//unmarshal request body
	err = json.Unmarshal(data, &hook)
	if err != nil {
		if _, err = f.Write([]byte("Failed to read request:\n" + err.Error())); err != nil {
			fmt.Println(err)
		}
		return
	}

	if hook.Ref != "refs/heads/master" {
		if _, err = f.Write([]byte("this is no main branch\n")); err != nil {
			fmt.Println(err)
		}
		return
	}

	cmd := exec.Command("git", "pull")
	wd, err := os.Getwd()
	if err != nil {
		panic(err)
	}
	parent := filepath.Dir(wd)
	//fmt.Println(parent)
	cmd.Dir = parent
	/*cmd.Env = os.Environ()
	cmd.Env = append(cmd.Env,
		"TK_PUBLIC_KEY=" + pub,
		"TK_PRIVATE_KEY=" + priv,
	)*/

	var out bytes.Buffer
	var stderr bytes.Buffer
	cmd.Stdout = &out
	cmd.Stderr = &stderr
	err = cmd.Run()
	if err != nil {
		if _, err = f.Write([]byte(fmt.Sprint(err) + ": " + stderr.String())); err != nil {
			fmt.Println(err)
		}
		return
	}
	result := out.String()
	if _, err = f.Write([]byte(result)); err != nil {
		fmt.Println(err)
	}

	if err = f.Close(); err != nil {
		fmt.Println(err)
	}

	fmt.Fprintf(w, "ok")
}
