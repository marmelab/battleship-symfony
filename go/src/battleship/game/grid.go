package game

// Grid is the board on which the battle ships are positionned
type Grid struct {
	Size  int
	Ships []Ship
	Shots []Shot
}

// NewGrid create a square grid of the given size
func NewGrid(gridSize int) Grid {
	return Grid{gridSize, []Ship{}, []Shot{}}
}

// AddShip add a ship to the grid
func AddShip(grid Grid, ship Ship) Grid {
	grid.Ships = append(grid.Ships, ship)
	return grid
}

// AddShot add a shoot to the grid
func AddShot(grid Grid, shot Shot) Grid {
	grid.Shots = append(grid.Shots, shot)
	return grid
}

// GetNextBestShots returns the list of possible next shots given the previous one
func GetNextBestShots(grid Grid) []Cell {
	nextShots := []Cell{}

	for _, shoot := range grid.Shots {

		if shoot.State == HIT {
			leftHit := leftCellIsHit(shoot.Cell, grid)
			rightHit := rightCellIsHit(shoot.Cell, grid)
			topHit := topCellIsHit(shoot.Cell, grid)
			bottomHit := bottomCellIsHit(shoot.Cell, grid)

			if leftHit {
				leftShots := HuntLeftCell(shoot.Cell, grid, nextShots)
				nextShots = appendShotsIfNotExist(nextShots, leftShots)
			}

			if rightHit {
				rightShots := HuntRightCell(shoot.Cell, grid, nextShots)
				nextShots = appendShotsIfNotExist(nextShots, rightShots)
			}

			if topHit {
				topShots := HuntTopCell(shoot.Cell, grid, nextShots)
				nextShots = appendShotsIfNotExist(nextShots, topShots)
			}

			if bottomHit {
				bottomShots := HuntBottomCell(shoot.Cell, grid, nextShots)
				nextShots = appendShotsIfNotExist(nextShots, bottomShots)
			}
		}
	}

	if len(nextShots) == 0 {
		for _, shoot := range grid.Shots {
			if shoot.State == HIT {
				nextShots = HuntTopCell(shoot.Cell, grid, nextShots)
				nextShots = HuntLeftCell(shoot.Cell, grid, nextShots)
				nextShots = HuntRightCell(shoot.Cell, grid, nextShots)
				nextShots = HuntBottomCell(shoot.Cell, grid, nextShots)
			}
		}
	}

	return nextShots
}

func appendShotsIfNotExist(nextShots []Cell, shots []Cell) []Cell {
	for _, shoot := range shots {
		if !IsElementInList(shoot, nextShots) {
			nextShots = append(nextShots, shoot)
		}
	}

	return nextShots
}

// IsElementInList checks if an cell is in a list of cells
func IsElementInList(elem Cell, list []Cell) bool {
	for _, listElem := range list {
		if elem == listElem {
			return true
		}
	}

	return false
}

func leftCellIsHit(cell Cell, grid Grid) bool {
	leftCell := Cell{cell.Row, cell.Column - 1}
	for _, shoot := range grid.Shots {
		if shoot.Cell == leftCell && isHit(shoot) {
			return true
		}
	}

	return false
}

func topCellIsHit(cell Cell, grid Grid) bool {
	topCell := Cell{cell.Row - 1, cell.Column}
	for _, shoot := range grid.Shots {
		if shoot.Cell == topCell && isHit(shoot) {
			return true
		}
	}

	return false
}

func bottomCellIsHit(cell Cell, grid Grid) bool {
	bottomCell := Cell{cell.Row + 1, cell.Column}
	for _, shoot := range grid.Shots {
		if shoot.Cell == bottomCell && isHit(shoot) {
			return true
		}
	}

	return false
}

func rightCellIsHit(cell Cell, grid Grid) bool {
	rightCell := Cell{cell.Row, cell.Column + 1}

	for _, shoot := range grid.Shots {
		if shoot.Cell == rightCell && isHit(shoot) {
			return true
		}
	}

	return false
}

func isHit(shoot Shot) bool {
	return shoot.State == HIT
}

// HuntTopCell get the first not hit cell at the top of the given cell
func HuntTopCell(cell Cell, grid Grid, nextShots []Cell) []Cell {
	if cell.Row <= 0 {
		return nextShots
	}

	topCell := Cell{cell.Row - 1, cell.Column}

	if hasHit(topCell, grid) {
		return HuntTopCell(topCell, grid, nextShots)
	} else if hasShoot(topCell, grid) {
		return nextShots
	}

	return append(nextShots, topCell)
}

// HuntLeftCell get the first not hit cell at the left of the given cell
func HuntLeftCell(cell Cell, grid Grid, nextShots []Cell) []Cell {
	if cell.Column <= 0 {
		return nextShots
	}

	leftCell := Cell{cell.Row, cell.Column - 1}

	if hasHit(leftCell, grid) {
		return HuntLeftCell(leftCell, grid, nextShots)
	} else if hasShoot(leftCell, grid) {
		return nextShots
	}

	return append(nextShots, leftCell)
}

// HuntRightCell get the first not hit cell at the right of the given cell
func HuntRightCell(cell Cell, grid Grid, nextShots []Cell) []Cell {
	if cell.Column >= grid.Size-1 {
		return nextShots
	}

	rightCell := Cell{cell.Row, cell.Column + 1}

	if hasHit(rightCell, grid) {
		return HuntRightCell(rightCell, grid, nextShots)
	} else if hasShoot(rightCell, grid) {
		return nextShots
	}

	return append(nextShots, rightCell)
}

// HuntBottomCell get the first not hit cell at the bottom of the given cell
func HuntBottomCell(cell Cell, grid Grid, nextShots []Cell) []Cell {
	if cell.Row >= grid.Size-1 {
		return nextShots
	}

	bottomCell := Cell{cell.Row + 1, cell.Column}

	if hasHit(bottomCell, grid) {
		return HuntBottomCell(bottomCell, grid, nextShots)
	} else if hasShoot(bottomCell, grid) {
		return nextShots
	}

	return append(nextShots, bottomCell)
}

func hasHit(cell Cell, grid Grid) bool {
	for _, shoot := range grid.Shots {
		if shoot.Cell == cell && shoot.State == HIT {
			return true
		}
	}

	return false
}

func hasShoot(cell Cell, grid Grid) bool {
	for _, shoot := range grid.Shots {
		if shoot.Cell == cell {
			return true
		}
	}

	return false
}
