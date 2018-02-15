import networkx as nx

G = nx.read_edgelist("edgeList.txt", create_using=nx.DiGraph())

pr = nx.pagerank(G,  alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)

fw = open('pagerank_refer5.txt','w')

for p in pr:
    fw.write("/Users/vickie/Documents/BigData/solr-7.1.0/BG/" + str(p) + "=" + str(pr[p]) + "\n")

fw.close()
