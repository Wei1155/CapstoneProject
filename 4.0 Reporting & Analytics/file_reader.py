def read_file(filename):
    f = open(filename, "r")
    lines = f.read().split("\n")
    f.close()
    return lines
